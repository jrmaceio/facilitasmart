<?php
/**
 * desabilitado, ficou so o relatorio, está rotina tem necessidade de alguns ajustes nos filtros e no sql
 * ContasReceberListInadimp Listing
 * @author  <your name here>
 */
class ContasReceberListInadimp extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    private $string; // conversoes
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->string = new StringsUtil;
        
        // creates the form
        $this->form = new TQuickForm('form_search_ContasReceber');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Inadimplência');
        

        // create the form fields
        $id = new TEntry('id');
        //$imovel_id = new TEntry('imovel_id');
        $mes_ref = new TEntry('mes_ref');
        $cobranca = new TEntry('cobranca');
        $classe_id = new TEntry('classe_id');
        $unidade_id = new TEntry('unidade_id');
        $unidade_nome = new TEntry('unidade_nome');
        $dt_vencimento = new TDate('dt_vencimento');
        //$descricao = new TEntry('descricao');
        $situacao = new TEntry('situacao');
        
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $correcao = new TEntry('correcao');

        $id->setSize(50);
        $mes_ref->setSize(100);
        $cobranca->setSize(50);
        $unidade_id->setSize(50);
        $unidade_nome->setSize(500);
        $classe_id->setSize(50);
        $situacao->setSize(50);
        $dt_vencimento->setSize(100);

        $multa->setSize(80);
        $juros->setSize(80);
        $correcao->setSize(80);        
        
        $unidade_nome->setEditable(FALSE);
        
        $multa->setNumericMask(2, ',', '.');
        $juros->setNumericMask(3, ',', '.');
        $correcao->setNumericMask(2, ',', '.');
         
        // add the fields
        //$this->form->addQuickField('Id', $id,  200 );
        //$this->form->addQuickField('Classe Id', $classe_id,  200 );
        //$this->form->addQuickField('Mes Ref', $mes_ref,  200 );
        //$this->form->addQuickField('Situacao', $situacao,  200 );
        
        $this->form->addQuickFields('% Multa', array($multa,
        new TLabel('% Juros ao dia'),$juros, 
        new TLabel('Correção'), $correcao
        ));
        
        $this->form->addQuickFields('Id', array($id, 
        new TLabel('Classe Id.............'),$classe_id, new TLabel('Situacao....'), $situacao,
        new TLabel('Mês Referência'),$mes_ref,
        new TLabel('Até Data'),$dt_vencimento));
        
        //$this->form->addQuickField('Imovel Id', $imovel_id,  200 );

        //$this->form->addQuickField('Cobranca', $cobranca,  200 );

        //$this->form->addQuickField('Unidade Id', $unidade_id,  200 );
        //$this->form->addQuickField('Dt Vencimento', $dt_vencimento,  200 );
        
        $this->form->addQuickFields('Cobrança', array($cobranca, new TLabel('Unidade Id'),$unidade_id, 
        new TLabel('Proprietario'),$unidade_nome));

        // mascaras
        $dt_vencimento->setMask('dd/mm/yyyy');
        
        $dt_vencimento->setValue(Date('d/m/Y'));
        
        // set exit action for input_exit
        $exit_id_unidade = new TAction(array($this, 'onExitIdUnidade'));
        $unidade_id->setExitAction($exit_id_unidade);
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('ContasReceber_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $this->form->addQuickAction( 'Email Sol.Comprovante', new TAction(array($this, 'onCobranca')), 'fa:commenting-o  red' );
        //$this->form->addQuickAction( 'Carta Cob Template', new TAction(array($this, 'onCartaTemplate')), 'fa:usd  red' );
        $this->form->addQuickAction( 'Carta Solicitação Comprovante', new TAction(array($this, 'onCartaCobranca')), 'fa:usd  red' );
        
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mes Ref', 'left');
        $column_cobranca = new TDataGridColumn('cobranca', 'Cob.', 'left');
        $column_classe_id = new TDataGridColumn('classe_descricao', 'Classe', 'right');
        //$column_unidade_id = new TDataGridColumn('proprietario_nome', 'Unidade Id', 'right');
        $column_unidade_id = new TDataGridColumn('unidade_desc', 'Unidade', 'right');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Dt Vencimento', 'center');
        $column_dias = new TDataGridColumn('dias', 'Dias', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_multa = new TDataGridColumn('multa', 'Multa', 'center');
        $column_juros = new TDataGridColumn('juros', 'Juros', 'center');
        $column_proj  = new TDataGridColumn('proj', 'Vlr Proj', 'right');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_imovel_id);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_cobranca);
        //$this->datagrid->addColumn($column_tipo_lancamento);
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_dias);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_multa);
        $this->datagrid->addColumn($column_juros);
        $this->datagrid->addColumn($column_proj);
        
        // create EDIT action
        //$action_edit = new TDataGridAction(array('ContasReceberForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        
        // create DELETE action
        //$action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        //$action_del->setLabel(_t('Delete'));
        //$action_del->setImage('fa:trash-o red fa-lg');
        //$action_del->setField('id');
        //$this->datagrid->addAction($action_del);
        
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
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        // mostrar o mes ref e imovel selecionado
        try
        {
            TTransaction::open('facilita');
            $logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Imóvel : ' . 
                        TSession::getValue('id_imovel')  . ' - ' . $logado->resumo));
        parent::add($container);
    }
    
    
    public function onShowRecebimento($param)
    {
        $datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
                
        
        $datagrid->addQuickColumn('Id', 'id', 'right');
        //$datagrid->addQuickColumn('Imovel Id', 'imovel_id', 'right');
        $datagrid->addQuickColumn('Mes Ref', 'mes_ref', 'left');
        //$datagrid->addQuickColumn('Cobranca', 'cobranca', 'left');
        //$datagrid->addQuickColumn('Tipo Lancamento', 'tipo_lancamento', 'left');
        //$datagrid->addQuickColumn('Classe Id', 'classe_id', 'right');
        $datagrid->addQuickColumn('Unidade', 'unidade_id', 'right');
        //$datagrid->addQuickColumn('Dt Lancamento', 'dt_lancamento', 'left');
        $datagrid->addQuickColumn('Dt Venc', 'dt_vencimento', 'left');
        //$datagrid->addQuickColumn('Valor', 'valor', 'left');
        
        //$datagrid->addQuickColumn('Descricao', 'descricao', 'left');
        $datagrid->addQuickColumn('Sit', 'situacao', 'left');
        $datagrid->addQuickColumn('Dt Pag.', 'dt_pagamento', 'left');
        $datagrid->addQuickColumn('Pagamento', 'valor_pago', 'left');
        
        //$datagrid->addQuickColumn('Desconto', 'desconto', 'left');
        //$datagrid->addQuickColumn('Juros', 'juros', 'left');
        //$datagrid->addQuickColumn('Multa', 'multa', 'left');
        //$datagrid->addQuickColumn('Correcao', 'correcao', 'left');
//        $datagrid->addQuickColumn('Dt Ultima Alteracao', 'dt_ultima_alteracao', 'left');
        ///$datagrid->addQuickColumn('Usuario Id', 'usuario_id', 'right');
                
        // create the datagrid model
        $datagrid->createModel();
        
        TTransaction::open('facilita'); 
        $conn = TTransaction::get(); 
        $imovel_id = TSession::getValue('id_imovel');
        $mes_ref = TSession::getValue('mesref');
        
        //var_dump($param);
        
        $sql = " (SELECT id, mes_ref, unidade_id, dt_vencimento, situacao, dt_pagamento, valor_pago 
        FROM contas_receber 
        where 
        unidade_id = {$param['unidade_id']} and 
        situacao = 1
        order by dt_vencimento asc) ";
                
        $colunas = $conn->query($sql);

        $message = 'Recebimentos'.'<br>';
        
        foreach ($colunas as $coluna) 
        { 
         
          $coluna['id'] = str_pad($coluna['id'],6,'0', STR_PAD_LEFT);
          $coluna['dt_vencimento'] ? $coluna['dt_vencimento'] = $this->string->formatDateBR($coluna['dt_vencimento']) : null;
          $coluna['dt_pagamento'] ? $coluna['dt_pagamento'] = $this->string->formatDateBR($coluna['dt_pagamento']) : null;
          $coluna['valor_pago'] ? $coluna['valor_pago'] = number_format($coluna['valor_pago'], 2, ',', '.') : null;
                
          // creates a string with the form element's values
          $message.= 'id : ' . $coluna['id'] . ' Venc.: ' . $coluna['dt_vencimento'] . 
          ' Dt.Pagamento: ' . $coluna['dt_pagamento'] .
          ' Valor Pago: ' . $coluna['valor_pago'] .'<br>';
          
        } 
        
                
        TTransaction::close(); 
        
 
        // show the message
        new TMessage('info', $message);
        
    }    
    
   
    public static function onExitIdUnidade($param)
    {
        try
        {
            if ($param['unidade_id']=='') {
              $obj = new StdClass;
              $obj->unidade_nome = '';
              TForm::sendData('form_search_ContasReceber', $obj);
        
              return;
            }
            
            TTransaction::open('facilita');
            $unidade_desc = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
            $unidade_prop_nome = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
                $obj = new StdClass;
        //$obj->unidade_descricao = $unidade_desc;
        $obj->unidade_nome = $unidade_desc . ' - ' . $unidade_prop_nome;
        TForm::sendData('form_search_ContasReceber', $obj);
        
        
        //new TMessage('info', 'Message on field exit. <br>You have typed: ' . $param['input_exit']);
    }
    
    public function onCartaTemplate($param)
    {       
        try
        {
             // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            //$limit = 10;
            $limit = 600;
            
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListInadimp_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_id')); // add the session filter
            }


            //if (TSession::getValue('ContasReceberListInadimp_filter_imovel_id')) {
            //    $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_imovel_id')); // add the session filter
            //}
            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter


            if (TSession::getValue('ContasReceberListInadimp_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_cobranca')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_cobranca')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_descricao')); // add the session filter
            }
        
            // filtro da situação 0 = não pago
            $criteria->add(new TFilter('situacao', '=', "0"));
            
   //         if (TSession::getValue('ContasReceberListInadimp_filter_situacao')) {
     //           $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_situacao')); // add the session filter
       //     }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
             
            $total_lanc = 0;
            $total_proj = 0;
            $total_multa = 0;
            $total_juros = 0;

           // unidades
           $descricao = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
           $proprietario = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
           //$tr->addCell($descricao.' - '.$proprietario, 'left', 'normal', 9);
           
           if ($objects)
            {
            
                // importa o PHPWord
                //require_once('lib2/PHPWord/PhpWord.php');
                $PHPWord = new PHPWord\PHPWord\PHPWord();
                //var_dump($PHPWord);
                $document = $PHPWord->loadTemplate('app/output/TemplateCartaCobranca1.odt');

                //$nomeloteario = strtr($cliente->NMCLIENTE, $map);
                $document->setValue('unidade',$proprietario); // nome loteario tag que esta dentro da template .DOCX 
                
                foreach ($objects as $object)
                {
                    //var_dump($object);
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $total_lanc += $object->valor;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    $total_proj += $object->proj;
                    
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                
                    // 2a verificação de segurança, não imprime se unidade_id do contas receber for diferente de unidade usada na consulta
                    if ( $object->unidade_id != $param['unidade_id'] ) {
                      new TMessage('info', 'Divergência na pesquisa e impressão, refaça consulta !');
                      return;
            
                    }
                    
                    $row = $table3->addRow('<b> </b>');
                    $cell = $row->addCell($object->dt_vencimento);
                    $cell->style = 'width: 200px;';
                    $cell = $row->addCell($object->valor);
                    //$designer->Cell( 70, 9, $object->dt_vencimento, 0, 0, 'C', $fill);
                    //$designer->Cell(160, 9, $conta, 0, 0, 'L', $fill);
                    //$designer->Cell(70, 9, $object->valor, 0, 0, 'R', $fill);
                    //$designer->Cell(70, 9, $object->multa, 0, 0, 'R', $fill);
                    //$designer->Cell(70, 9, $object->juros, 0, 0, 'R', $fill);
                    //$designer->Cell(70, 9, $object->proj, 0, 0, 'R', $fill);
                    //$designer->Ln(12);
                    
                    
                }
            }
           
            
            $total_lanc ? $total_lanc = number_format($total_lanc, 2, ',', '.') : null;
            $total_juros ? $total_juros = number_format($total_juros, 2, ',', '.') : null;
            $total_multa ? $total_multa = number_format($total_multa, 2, ',', '.') : null;
            $total_proj ? $total_proj = number_format($total_proj, 2, ',', '.') : null;
            
        
           // verificação de segurança, se o usuario não deu click em pesquisar apos preencher a unidades
           $debito_seguranca = Unidades::RetornaInadimpUnidade($param['unidade_id']);
           $debito_seguranca ? $debito_seguranca = number_format($debito_seguranca, 2, ',', '.') : null;
            
           if ( $debito_seguranca != $total_lanc ) {
                new TMessage('info', 'Confira a carta, débito divergente ao impresso (Débito total R$ ' . $debito_seguranca . ' # Débito Carta R$ ' . $total_lanc . ') !!!');    
           }

  
           
           // fecha a transação
           TTransaction::close();
           
           // cria o arquivo final
           $nome = 'Carta1'.date('dmY').'.odt';
           $document->save("app/output/{$nome}");
           // open the report file
           parent::openFile("app/output/{$nome}"); 
           
          
           
           //new TMessage('info', 'Email enviado com sucesso');
           
          try
          {
           // TTransaction::open('facilita'); // open a transaction
          //  $object = UsuarioImovel::find($selecionado->id); // load the object
          //  $object->dt_envio_senha=date("Y-m-d");
          //  $object->store();
          //  TTransaction::close(); // close the transaction
          
          }
          catch (Exception $e) // in case of exception
          {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
          }
                        
               
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
    }

    
    public function onCobranca($param)
    {       
        try
        {
             // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 600;
            
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListInadimp_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_id')); // add the session filter
            }


            //if (TSession::getValue('ContasReceberListInadimp_filter_imovel_id')) {
            //    $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_imovel_id')); // add the session filter
            //}
            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter


            if (TSession::getValue('ContasReceberListInadimp_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_cobranca')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_cobranca')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_descricao')); // add the session filter
            }
        
            // filtro da situação 0 = não pago
            $criteria->add(new TFilter('situacao', '=', "0"));
            
   //         if (TSession::getValue('ContasReceberListInadimp_filter_situacao')) {
     //           $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_situacao')); // add the session filter
       //     }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
             
            $total_lanc = 0;
            $total_proj = 0;
            $total_multa = 0;
            $total_juros = 0;

           $email2 = 'facilitahomeservice@gmail.com';
           
           $table = new TTable;
           $table->border = 0;
           $table1 = new TTable;
           $table1->border = 1;
           $table2 = new TTable;
           $table2->border = 1;
           $table3 = new TTable;
           $table3->border = 1;
           $table4 = new TTable;
           $table4->border = 1;
           
           $table5 = new TTable;
           $table5->border = 1;
           
           $imagem = new TImage('app/images/facilita.png');
           $imagem->height=63;
           $imagem->width=96;
           
           $row = $table->addRow();
           $cell = $row->addCell( $imagem );
           $cell->style = 'width: 100px;';
           $row = $table->addRow();
           $cell = $row->addCell("Prezado Sr(a).: <br /> ");
           $cell->style = 'width: 700px;';
            
           // unidades
           $descricao = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
           $proprietario = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
           $email = Unidades::RetornaProprietarioEmail($param['unidade_id']);
          
           //XXXXXXXXX///mudar no ambiente de homologação =====> $selecionado->system_user_login
           $email1 = 'jrmaceio09@gmail.com';//$solicitante->email1;
           //$email1 = $email; // email do proprietario
           
           $row = $table->addRow();
           $cell = $row->addCell($descricao.' - '.$proprietario." <br /> ");
           $row = $table->addRow();
           $cell = $row->addCell($email." <br /> ");
                       
           $row = $table->addRow();
           $row->addCell('<span style="color: DarkOliveGreen;"><br /><u>Solicitação de comprovante de pagamento: </b>  <br /> </u> </span>');   
           $row = $table->addRow();
           $row = $table->addRow();
           $cell = $row->addCell( '<br /> Vimos atraves do presente, solicitar a Vossa Senhoria que nos envio cópia(s) do(s) mês(es) abaixo relacionado(s), ' );
           $row = $table->addRow();
           $cell = $row->addCell( 'tendo em vista que não consta(m) em nosso sistema tal(is) pagamento(s).' );
           $row = $table->addRow();
           $row = $table->addRow();
           $cell = $row->addCell( '<br /> Contamos com sua colaboração para que possamos solucionar esta questão o quanto antes.' );
           $row = $table->addRow();
           $row = $table->addRow();
           $cell = $row->addCell( '<br />Caso já tenha enviado o comprovante desconsiderar este. Havendo débito aguardamos seu contato para que' );
           $row = $table->addRow();
           $cell = $row->addCell( 'possamos entrar em acordo. ' . "<br /> ");
           $row = $table->addRow();
           $row = $table->addRow();
           $cell = $row->addCell( '<br /> Os valores apresentados estão sem correção, devendo serem calculados no ato do pagamento.' );
           $row = $table->addRow();
           $cell = $row->addCell( '<br />'); 
           
           $row = $table1->addRow();
           $row->addCell('<b>Data/Hora:</b>');
           $row->addCell(date('d/m/Y H:i'));
           
          
           $row = $table->addRow();
           $cell = $row->addCell($table1);
           $cell->colspan=2;
           
                                 
           $row = $table->addRow();
           $cell = $row->addCell($table3);
           $cell->colspan=2;
           
           $row = $table->addRow();
           $row = $table3->addRow();
           $cell = $row->addCell('<b>Classe</b>');
           $cell = $row->addCell('<b>Vencimento</b>');
           $cell->style = 'width: 200px;';
           $cell = $row->addCell('<b>Valor</b>');
           
           if ($objects)
            {
                foreach ($objects as $object)
                {
                    
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                    
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $total_lanc += $object->valor;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    $total_proj += $object->proj;
                    
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                
                    // 2a verificação de segurança, não imprime se unidade_id do contas receber for diferente de unidade usada na consulta
                    if ( $object->unidade_id != $param['unidade_id'] ) {
                      new TMessage('info', 'Divergência na pesquisa e impressão, refaça consulta !');
                      return;
            
                    }
                    
                    $row = $table3->addRow('<b> </b>');
                    $cell = $row->addCell($conta);
                    $cell = $row->addCell($object->dt_vencimento);
                    $cell->style = 'width: 200px;';
                    $cell = $row->addCell($object->valor);
                 
                    
                }
            }
           
            $total_lanc ? $total_lanc = number_format($total_lanc, 2, ',', '.') : null;
            $total_juros ? $total_juros = number_format($total_juros, 2, ',', '.') : null;
            $total_multa ? $total_multa = number_format($total_multa, 2, ',', '.') : null;
            $total_proj ? $total_proj = number_format($total_proj, 2, ',', '.') : null;
           
            $row = $table3->addRow('<b> </b>');
            $cell = $row->addCell('');
            $cell = $row->addCell('Total Lançado');
            $cell->style = 'width: 200px;';
            $cell = $row->addCell($total_lanc);
            
           $row = $table->addRow();
           $cell = $row->addCell( '<br />'); 
                    
          /* $row = $table->addRow();
           $cell = $row->addCell($table5);
           $cell->colspan=2;
        
            $row = $table->addRow();
            $row = $table5->addRow();
            $cell = $row->addCell('<b>Lançado</b>');
            $cell = $row->addCell('<b>Multa</b>');
            $cell = $row->addCell('<b>Juros</b>');
            $cell = $row->addCell('<b>Projetado</b>');
           
            $row = $table5->addRow('<b> </b>');
            $cell = $row->addCell($total_lanc);
            $cell = $row->addCell($total_multa);
            $cell = $row->addCell($total_juros);
            $cell = $row->addCell($total_proj);*/
     
           $row = $table->addRow();
           $cell = $row->addCell($table4);
           $cell->colspan=2;
        
           $row = $table4->addRow();
           $cell = $row->addCell('<span style="color: red;"><b>Importante:</b></span> Não responda esse e-mail, fale com (82) 4102-0015 ou email: facilitahomeservice@gmail.com');
           $row = $table->addRow();
           $cell = $row->addCell( '<br />'); 
           $row = $table4->addRow();
           $cell = $row->addCell('<span style="color: red;"><b>Confidencial:</b></span> Esta mensagem, incluindo os seus anexos, contém informações confidenciais destinadas a indivíduo e propósito específicos, sendo protegida por lei. Caso você não seja a pessoa a quem foi dirigida a mensagem, deve apagá-la. É terminantemente proibida a utilização, acesso, cópia ou divulgação não autorizada das informações presentes nesta mensagem.');
           
                 
                           
           // fecha a transação
           TTransaction::close();
           
           $ini = parse_ini_file('app/config/email.ini');
           
           $mail = new TMail;
           $mail->setFrom($ini['from'], $ini['name']);
           $mail->setSubject('Facilita criou um ticket para voce');
           $mail->setHtmlBody($table);
           $mail->addAddress($email1);
           $mail->addCC($email2);
           //$mail->addBCC('jrmaceio09@gmail.com');
           
           // Se tiver anexo
           if (isset($target_file))
           {
           $mail->addAttach($target_file);
           }
           $mail->SetUseSmtp();
           $mail->SetSmtpHost($ini['host'], $ini['port']);
           $mail->SetSmtpUser($ini['user'], $ini['pass']);
           //senao configurar em ini, nao pode ficar em branco $mail->setReplyTo($ini['repl']);
           
           // verificação de segurança, se o usuario não deu click em pesquisar apos preencher a unidades
           $debito_seguranca = Unidades::RetornaInadimpUnidade($param['unidade_id']);
           $debito_seguranca ? $debito_seguranca = number_format($debito_seguranca, 2, ',', '.') : null;
            
           if ( $debito_seguranca != $total_lanc ) {
             new TMessage('info', 'Email não enviado, débito divergente (Débito total R$ ' . $debito_seguranca . ' # Débito Carta R$ ' . $total_lanc . ') !!!');    
           }else
           {          
             $mail->send();
           }
          
           
           new TMessage('info', 'Email enviado com sucesso');
           
                      
               
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
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
            
            TTransaction::open('facilita'); // open a transaction with database
            $object = new ContasReceber($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
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
        TSession::setValue('ContasReceberListInadimp_filter_id',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_imovel_id',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_mes_ref',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_cobranca',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_classe_id',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_unidade_id',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_dt_vencimento',   NULL);
        //TSession::setValue('ContasReceberListInadimp_filter_descricao',   NULL);
        TSession::setValue('ContasReceberListInadimp_filter_situacao',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_id',   $filter); // stores the filter in the session
        }

        // filtra pelo imovel escolhido em mes_ref imoveis
        $filter = new TFilter('imovel_id', '=', TSession::getValue('id_imovel')); // create the filter
        TSession::setValue('ContasReceberListInadimp_filter_imovel_id',   $filter); // stores the filter in the session
    

        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', 'like', "%{$data->mes_ref}%"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_mes_ref',   $filter); // stores the filter in the session
        }


        if (isset($data->cobranca) AND ($data->cobranca)) {
            $filter = new TFilter('cobranca', 'like', "%{$data->cobranca}%"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_cobranca',   $filter); // stores the filter in the session
        }


        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', 'like', "%{$data->classe_id}%"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_classe_id',   $filter); // stores the filter in the session
        }


        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_unidade_id',   $filter); // stores the filter in the session
        }


        if (isset($data->dt_vencimento) AND ($data->dt_vencimento)) {
            $data->dt_vencimento = $this->string->formatDate($data->dt_vencimento);
            $filter = new TFilter('dt_vencimento', '<=', "{$data->dt_vencimento}"); // create the filter
            $data->dt_vencimento  = $this->string->formatDateBR($data->dt_vencimento );
            TSession::setValue('ContasReceberListInadimp_filter_dt_vencimento',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('ContasReceberListInadimp_filter_descricao',   $filter); // stores the filter in the session
        }


        // filtro da situação 0 = não pago
        $filter = new TFilter('situacao', '=', "0"); // create the filter
        TSession::setValue('ContasReceberListInadimp_filter_situacao',   $filter); // stores the filter in the session
        
        $imovel_id = TSession::getValue('id_imovel');
               
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
            // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 600;
            //$limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListInadimp_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_id')); // add the session filter
            }


            //if (TSession::getValue('ContasReceberListInadimp_filter_imovel_id')) {
            //    $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_imovel_id')); // add the session filter
            //}
            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter


            if (TSession::getValue('ContasReceberListInadimp_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_cobranca')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_cobranca')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_descricao')); // add the session filter
            }
        
            // filtro da situação 0 = não pago
            $criteria->add(new TFilter('situacao', '=', "0"));
            
   //         if (TSession::getValue('ContasReceberListInadimp_filter_situacao')) {
     //           $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_situacao')); // add the session filter
       //     }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
             
            $total_lanc = 0;
            $total_proj = 0;
           
            $this->datagrid->clear();
            
            if (empty($objects)) 
            {
               new TMessage('info', 'Verifique se escolheu o Imóvel desejado ' . '<br>' . 
               'Imóvel Selecionado : ' . TSession::getValue('resumo') . '<br>' . 
               'Mês Ref.: ' . TSession::getValue('mesref'));
           
            }
            
            if ( $formdata->multa ) {
                $perc_multa = str_replace(",",".", $formdata->multa);
            }else {
                $perc_multa = 0.00;
            }
            
            if ( $formdata->juros ) {
                $perc_juros = str_replace(",",".",$formdata->juros);
            }else {
                $perc_juros = 0.000;
            }
            
            if ($objects)
            {
               
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = $perc_juros * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = (($perc_multa * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $total_lanc += $object->valor;
                    $total_proj += $object->proj;
                    
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                    
                    //  the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            $total_lanc = number_format($total_lanc,2,",",".");
            $total_proj = number_format($total_proj,2,",","."); 
            
            $item = new StdClass; 
            $item->id = ' ';
            $item->mes_ref = ' ';
            $item->cobranca =' ';
            $item->classe_descricao = ' ';
            $item->unidade_desc = ' ';
            $item->unidade_id = ' ';
            $item->classe_id = ' ';
            $item->situacao = ' ';
            $item->multa = ' ';
            $item->juros = ' ';
            $item->proj = "<font color = red><b>$total_proj</b></font>";
            $item->dt_vencimento = '<b>TOTAL:</b>';
            $item->dias = ' ';
            $item->valor = "<font color = red><b>$total_lanc</b></font>";
            $this->datagrid->addItem($item); 
            
            // libera as variáveis da memória
            unset($total);
            unset($item); 
                        
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
            TTransaction::open('facilita'); // open a transaction with database
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
    
   public function onCartaCobranca($param)
   {
     try
        {
             // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 600;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListInadimp_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_id')); // add the session filter
            }

            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter


            if (TSession::getValue('ContasReceberListInadimp_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_cobranca')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_cobranca')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_descricao')); // add the session filter
            }
        
            // filtro da situação 0 = não pago
            $criteria->add(new TFilter('situacao', '=', "0"));
            
   //         if (TSession::getValue('ContasReceberListInadimp_filter_situacao')) {
     //           $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_situacao')); // add the session filter
       //     }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
             
            $total_lanc = 0;
            $total_proj = 0;
            $total_multa = 0;
            $total_juros = 0;
            
            $this->form->validate();
            
            $designer = new TPDFDesigner;
            //$fpdf = $designer->getNativeWriter();
            //$this->Image('app/images/facilita_marca_dagua.png',10,6,30);
            $designer->fromXml('app/reports/carta_cobranca1.pdf.xml');
            $designer->generate();
            
            // imovel
            $imovel = Imoveis::NomeImovel(TSession::getValue('id_imovel'));
            $fill = TRUE;
            $designer->gotoAnchorXY('imovel');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            $designer->Cell( 400, 10, $imovel, 0, 0, 'L', $fill);
            
            // unidades
            $descricao = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
            $proprietario = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
            //$tr->addCell($descricao.' - '.$proprietario, 'left', 'normal', 9);
            
            $fill = TRUE;
            $designer->gotoAnchorXY('unidade');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            $designer->Cell( 400, 10, $param['unidade_id'] . ' ' . $descricao.'-'.$proprietario, 0, 0, 'L', $fill);
            
            $fill = TRUE;
            $designer->gotoAnchorXY('detalhe');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            
                            
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    //var_dump($object);
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $total_lanc += $object->valor;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    $total_proj += $object->proj;
                    
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                
                    // 2a verificação de segurança, não imprime se unidade_id do contas receber for diferente de unidade usada na consulta
                    if ( $object->unidade_id != $param['unidade_id'] ) {
                      new TMessage('info', 'Divergência na pesquisa e impressão, refaça consulta !');
                      return;
            
                    }
                    
                    $designer->gotoAnchorX('detalhe');
                    //Cell(width,  height, texto, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
                    $designer->Cell( 30, 9, $object->id, 0, 0, 'C', $fill);
                    $designer->Cell( 70, 9, $object->dt_vencimento, 0, 0, 'C', $fill);
                    $designer->Cell(140, 9, utf8_decode($conta), 0, 0, 'L', $fill);
                    $designer->Cell(70, 9, $object->valor, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->multa, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->juros, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->proj, 0, 0, 'R', $fill);
                    $designer->Ln(12);
                    
                    // grid background
                    $fill = !$fill;
                }
            }
            
            $total_lanc ? $total_lanc = number_format($total_lanc, 2, ',', '.') : null;
            $total_juros ? $total_juros = number_format($total_juros, 2, ',', '.') : null;
            $total_multa ? $total_multa = number_format($total_multa, 2, ',', '.') : null;
            $total_proj ? $total_proj = number_format($total_proj, 2, ',', '.') : null;
            
            $designer->gotoAnchorX('detalhe');
            //Cell(width,  height, texto, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
            $designer->Cell( 80, 9, '', 0, 0, 'C', $fill);
            $designer->Cell(160, 9, 'Totais', 0, 0, 'L', $fill);
            $designer->Cell(70, 9, $total_lanc, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_multa, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_juros, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_proj, 0, 0, 'R', $fill);
            $designer->Ln(12);
            
            $designer->writeAtAnchor('data', date('d-m-Y'));
            
            $file = 'app/output/carta_cobranca1.pdf';
            
            if (!file_exists($file) OR is_writable($file))
            {
                $designer->save($file);
                parent::openFile($file);
            }
            else
            {
                throw new Exception(_t('Permission denied') . ': ' . $file);
            }
            
            // verificação de segurança, se o usuario não deu click em pesquisar apos preencher a unidades
            $debito_seguranca = Unidades::RetornaInadimpUnidade($param['unidade_id']);
            $debito_seguranca ? $debito_seguranca = number_format($debito_seguranca, 2, ',', '.') : null;
            
            if ( $debito_seguranca != $total_lanc ) {
                new TMessage('info', 'Confira a carta, débito divergente ao impresso (Débito total R$ ' . $debito_seguranca . ' # Débito Carta R$ ' . $total_lanc . ') !!!');    
            }
            
            new TMessage('info', 'Relatorio gerado. Por favor, habilite popups no navegador (somente para web).');
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
    }        
        
   public function onCartaCobranca2($param)
   {
     try
        {
             // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for ContasReceber
            $repository = new TRepository('ContasReceber');
            $limit = 600;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('ContasReceberListInadimp_filter_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_id')); // add the session filter
            }

            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter


            if (TSession::getValue('ContasReceberListInadimp_filter_mes_ref')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_mes_ref')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_cobranca')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_cobranca')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_classe_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_classe_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_unidade_id')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_unidade_id')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('ContasReceberListInadimp_filter_descricao')) {
                $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_descricao')); // add the session filter
            }
        
            // filtro da situação 0 = não pago
            $criteria->add(new TFilter('situacao', '=', "0"));
            
   //         if (TSession::getValue('ContasReceberListInadimp_filter_situacao')) {
     //           $criteria->add(TSession::getValue('ContasReceberListInadimp_filter_situacao')); // add the session filter
       //     }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
             
            $total_lanc = 0;
            $total_proj = 0;
            $total_multa = 0;
            $total_juros = 0;
            
            $this->form->validate();
            
            $designer = new TPDFDesigner;
            //$fpdf = $designer->getNativeWriter();
            //$this->Image('app/images/facilita_marca_dagua.png',10,6,30);
            $designer->fromXml('app/reports/carta_cobranca1.pdf.xml');
            $designer->generate();
            
            // imovel
            $imovel = Imoveis::NomeImovel(TSession::getValue('id_imovel'));
            $fill = TRUE;
            $designer->gotoAnchorXY('imovel');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            $designer->Cell( 400, 10, $imovel, 0, 0, 'L', $fill);
            
            // unidades
            $descricao = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
            $proprietario = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
            //$tr->addCell($descricao.' - '.$proprietario, 'left', 'normal', 9);
            
            $fill = TRUE;
            $designer->gotoAnchorXY('unidade');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            $designer->Cell( 400, 10, $param['unidade_id'] . ' ' . $descricao.'-'.$proprietario, 0, 0, 'L', $fill);
            
            $fill = TRUE;
            $designer->gotoAnchorXY('detalhe');
            $designer->SetFont('Arial', '', 10);
            $designer->setFillColorRGB( '#F9F9FF' );
            
                            
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    //var_dump($object);
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }

                    $object->multa = $multa;
                    $object->juros = $juros;
                    $object->proj  = $object->valor + $object->multa + $object->juros;   
  
                    $total_lanc += $object->valor;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    $total_proj += $object->proj;
                    
                    $object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
                    $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                    $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                    $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                    $object->proj ? $object->proj = number_format($object->proj, 2, ',', '.') : null;
                
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                
                    // 2a verificação de segurança, não imprime se unidade_id do contas receber for diferente de unidade usada na consulta
                    if ( $object->unidade_id != $param['unidade_id'] ) {
                      new TMessage('info', 'Divergência na pesquisa e impressão, refaça consulta !');
                      return;
            
                    }
                    
                    $designer->gotoAnchorX('detalhe');
                    //Cell(width,  height, texto, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
                    $designer->Cell( 30, 9, $object->id, 0, 0, 'C', $fill);
                    $designer->Cell( 70, 9, $object->dt_vencimento, 0, 0, 'C', $fill);
                    $designer->Cell(140, 9, utf8_decode($conta), 0, 0, 'L', $fill);
                    $designer->Cell(70, 9, $object->valor, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->multa, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->juros, 0, 0, 'R', $fill);
                    $designer->Cell(70, 9, $object->proj, 0, 0, 'R', $fill);
                    $designer->Ln(12);
                    
                    // grid background
                    $fill = !$fill;
                }
            }
            
            $total_lanc ? $total_lanc = number_format($total_lanc, 2, ',', '.') : null;
            $total_juros ? $total_juros = number_format($total_juros, 2, ',', '.') : null;
            $total_multa ? $total_multa = number_format($total_multa, 2, ',', '.') : null;
            $total_proj ? $total_proj = number_format($total_proj, 2, ',', '.') : null;
            
            $designer->gotoAnchorX('detalhe');
            //Cell(width,  height, texto, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
            $designer->Cell( 80, 9, '', 0, 0, 'C', $fill);
            $designer->Cell(160, 9, 'Totais', 0, 0, 'L', $fill);
            $designer->Cell(70, 9, $total_lanc, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_multa, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_juros, 0, 0, 'R', $fill);
            $designer->Cell(70, 9, $total_proj, 0, 0, 'R', $fill);
            $designer->Ln(12);
            
            $designer->writeAtAnchor('data', date('d-m-Y'));
            
            $file = 'app/output/carta_cobranca1.pdf';
            
            if (!file_exists($file) OR is_writable($file))
            {
                $designer->save($file);
                parent::openFile($file);
            }
            else
            {
                throw new Exception(_t('Permission denied') . ': ' . $file);
            }
            
            // verificação de segurança, se o usuario não deu click em pesquisar apos preencher a unidades
            $debito_seguranca = Unidades::RetornaInadimpUnidade($param['unidade_id']);
            $debito_seguranca ? $debito_seguranca = number_format($debito_seguranca, 2, ',', '.') : null;
            
            if ( $debito_seguranca != $total_lanc ) {
                new TMessage('info', 'Confira a carta, débito divergente ao impresso (Débito total R$ ' . $debito_seguranca . ' # Débito Carta R$ ' . $total_lanc . ') !!!');    
            }
            
            new TMessage('info', 'Relatorio gerado. Por favor, habilite popups no navegador (somente para web).');
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
    }           
 
    
}
