<?php
/**
 * @author  <your name here>
 */
class ContasReceberRelLiqDataPagamento extends TPage
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber_report');
        $this->form->setFormTitle('Contas a Receber Liquidadas por Data de Pagamento');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');
        
        //$unidade_id = new TEntry('unidade_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao} - {proprietario_nome}', 'descricao', $criteria);
        

        $dt_pag_inicio =new TDate('dt_pag_inicio');
        $dt_pag_inicio->setMask('dd/mm/yyyy');
        $dt_pag_fim =new TDate('dt_pag_fim');
        $dt_pag_fim->setMask('dd/mm/yyyy');
        
        //$classe_id = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        
        $mes_ref = new TEntry('mes_ref');
  
        $dt_lancamento = new TEntry('dt_lancamento');
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $valor = new TEntry('valor');

        $output_type = new TRadioGroup('output_type');

        //$situacao->setValue('0'); // em aberto 
        
        $classe_id->setSize('50%');
        //$situacao->setSize(50);
        $mes_ref->setSize('100%');
        $cobranca->setSize('50%');
        $unidade_id->setSize('50%');
        
        //$dt_liquidacao->setSize(100);
        $dt_pag_inicio->setSize('100%');
        $dt_pag_fim->setSize('100%');
        
        $tipo_lancamento->setSize('50%');
        
        // add the fields
        //$this->form->addQuickFields('Classe Id:', array($classe_id, 
        //new TLabel('Mês Referência:'),$mes_ref,
        //new TLabel('Cobrança:'),$cobranca,
        //new TLabel('Unidade Id:'),$unidade_id));

        $this->form->addFields( [new TLabel('Classe')], [$classe_id],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id],
                                [new TLabel('Cobrança')], [$cobranca]                                
                            );
        
        $this->form->addFields( [new TLabel('Dt. Pagamento Inicial')], [$dt_pag_inicio],
                                [new TLabel('Dt. Pagamento Final')], [$dt_pag_fim]                                
                            );
        
        $change_data = new TAction(array($this, 'onChangeData'));
        $dt_pag_inicio->setExitAction($change_data);
        $dt_pag_fim->setExitAction($change_data);

        //$this->form->addQuickField('Output', $output_type,  100 , new TRequiredValidator);
        $this->form->addFields( [new TLabel('Output')], [$output_type]);
 
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        // add the action button
        //$this->form->addQuickAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        // create the form actions
        $btn = $this->form->addAction( _t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        /////////////////////////////$container->add(TPanelGroup::pack('Relatório', $this->form));
        $container->add($this->form);
        
        // mostrar o mes ref e condominio selecionado
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
    
    public static function onChangeData($param)
    {
      
        $obj = new StdClass;
        $string = new StringsUtil;
        
        if(strlen($param['dt_pag_inicio']) == 10 && strlen($param['dt_pag_fim']) == 10)
        {
        
            if(strtotime($string->formatDate($param['dt_pag_fim'])) < strtotime($string->formatDate($param['dt_pag_inicio'])))
            {
    	        $obj->data_atividade_final = ''; 
    	        new TMessage('error', 'Data de pagamento final menor que data de pagamento inicial'); 
            }
        
        }
        
        TForm::sendData('form_ContasReceber_report', $obj, FALSE, FALSE);
       
    }
    
    /**
     * Generate the report
     */
    function onGenerate($param = NULL)
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
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_pagamento, unidade_id, mes_ref';
                $param['direction'] = 'asc';
            }
            
            $formdata->dt_pag_inicio = $string->formatDate($formdata->dt_pag_inicio);
            $formdata->dt_pag_fim = $string->formatDate($formdata->dt_pag_fim);
            
            $criteria->setProperties($param); // order, offset
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            if ($formdata->cobranca)
            {
                $criteria->add(new TFilter('cobranca', '=', "{$formdata->cobranca}"));
            }
    
            if ($formdata->classe_id)
            {
                $criteria->add(new TFilter('classe_id', '=', "{$formdata->classe_id}"));
            }
            
            // titulos pagos
            $criteria->add(new TFilter('situacao', '=', "1"));
            
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$formdata->mes_ref}"));
            }
            
            if ($formdata->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$formdata->unidade_id}"));
            }
            
            if ($formdata->dt_pag_inicio)
            {
                $criteria->add(new TFilter('dt_pagamento', 'between', $formdata->dt_pag_inicio, $formdata->dt_pag_fim)); 
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
            
                       
            if ($objects)
            {
                $widths = array(60,30,80,40,40,40,40,80,40,80);
                
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
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '8', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell(TSession::getValue('resumo'), 'center', 'header', 11);
                
                $tr->addRow();
                $tr->addCell('Relação de Recebimentos por data de pagamento - Período de : ' . $string->formatDateBR($formdata->dt_pag_inicio) . ' até ' . $string->formatDateBR($formdata->dt_pag_fim), 'center', 'header', 10);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Pagamento', 'left', 'cabecalho');
                $tr->addCell('Id', 'center', 'cabecalho');
                $tr->addCell('Unidade', 'center', 'cabecalho');
                $tr->addCell('Mes Ref', 'center', 'cabecalho');
                $tr->addCell('Vencim.', 'center', 'cabecalho');
                $tr->addCell('Liquidação', 'center', 'cabecalho');
                $tr->addCell('Atraso(d)', 'center', 'cabecalho');
                $tr->addCell('Pago', 'right', 'cabecalho');
                $tr->addCell('Tarifa', 'right', 'cabecalho');
                $tr->addCell('Creditado', 'right', 'cabecalho');

                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor_lancado = 0;
                $total_geral_valor_pago = 0;
                $total_geral_multa = 0;
                $total_geral_juros = 0;
                $total_geral_desc = 0;
                $total_geral_tarifa = 0;
                $total_geral_creditado = 0;
                
                $total_valor_lancado = 0;
                $total_valor_pago = 0;
                $total_multa = 0;
                $total_juros = 0;
                $total_desc = 0;
                $total_tarifa = 0;
                $total_creditado = 0;
               
                $unidade = '';
                $datapagamento = '';
                                                       
                // data rows
                foreach ($objects as $object)
                {
                    if (  $datapagamento == '' ) 
                    {
                        
                        $datapagamento = $object->dt_pagamento;
                        
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($datapagamento), 'left', 'normal', 10);
                    }
                    
                    
                    if ($datapagamento != $object->dt_pagamento){
                    
                        if ( $total_valor_lancado > 0 ) {
                            $tr->addRow();
                            $tr->addCell('', 'center', 'footer', 7);
                            //$tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                            //$tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'footer');
                            //$tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'footer');
                            //$tr->addCell(number_format($total_desc, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_tarifa, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_creditado, 2, ',', '.'), 'right', 'footer');
                            
                            // zera totalizadores
                            $total_valor_lancado = 0;
                            $total_valor_pago = 0;
                            $total_multa = 0;
                            $total_juros = 0;
                            $total_desc = 0;
                            $total_tarifa = 0;
                            $total_creditado = 0;
                    
                        }
                    
                        $datapagamento = $object->dt_pagamento;
                    
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($datapagamento), 'left', 'normal', 10);
                        
                    }
                
                    $unidade = new Unidade($object->unidade_id);
                    
                    $proprietario = new Pessoa($unidade->proprietario_id);
                                       
                    $style = $colour ? 'datap' : 'datai';

                    $tr->addRow();
                    $tr->addCell('', 'right', $style);

                    $tr->addCell($object->id, 'center', $style);
                    
                    //$tr->addCell($unidade->descricao, 'center', $style);
                    if (isset($unidade->descricao)) {
                        $tr->addCell($unidade->descricao, 'center', $style);
                        
                    } else {
                        $tr->addCell($object->descricao, 'center', $style);
                        
                    }
                    
                    $tr->addCell($object->mes_ref, 'center', $style);

                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_liquidacao), 'center', $style);
                    
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime($object->dt_pagamento);
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    $tr->addCell($dias, 'center', $style);

                    $tr->addCell(number_format($object->valor_pago, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->tarifa, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->valor_creditado, 2, ',', '.'), 'right', $style);

                    //$classificacao = new PlanoContas($object->classe_id);
                                        
                    $total_valor_lancado += $object->valor;
                    $total_valor_pago += $object->valor_pago;
                    $total_multa += $object->multa;
                    $total_juros += $object->juros;
                    $total_desc += $object->desconto;
                    $total_tarifa += $object->tarifa;
                    $total_creditado += $object->valor_creditado;
                    
                    $total_geral_valor_lancado += $object->valor;
                    $total_geral_valor_pago += $object->valor_pago;
                    $total_geral_multa += $object->multa;
                    $total_geral_juros += $object->juros;
                    $total_geral_desc += $object->desconto;
                    $total_geral_tarifa += $object->tarifa;
                    $total_geral_creditado += $object->valor_creditado;
                                        
                    
                    $colour = !$colour;
                }
                
                // totaliza ultimo dia impresso
                $tr->addRow();
                $tr->addCell('', 'center', 'footer', 7);
                $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_tarifa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_creditado, 2, ',', '.'), 'right', 'footer');
                
                // footer row
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 11);
                
                // footer row
                $tr->addRow();
                $tr->addCell('', 'center', 'footer', 7);
                $tr->addCell(number_format($total_geral_valor_pago, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_tarifa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_creditado, 2, ',', '.'), 'right', 'footer');
                 
                ///////////// totalizacao por classe de conta ///////////////
                $condominio = TSession::getValue('id_condominio');
                
                $conn2 = TTransaction::get();

                $sql2 = "SELECT contas_receber.classe_id, plano_contas.descricao, 
                        sum( contas_receber.valor ) as valor,
                        sum( contas_receber.valor_pago ) as valor_pago
                        FROM contas_receber 
                        INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
                        where 
                        contas_receber.condominio_id = " . $condominio . " and situacao = '1' and " .
                        "contas_receber.dt_pagamento >= '" . $formdata->dt_pag_inicio . "' and 
                        contas_receber.dt_pagamento <= '" . $formdata->dt_pag_fim . "' ";
                        ;
                

                if ($formdata->cobranca)
                {
                    $sql2 = $sql2 . 'and cobranca = ' . $formdata->cobranca;
                }
    
                if ($formdata->classe_id)
                {
                    $sql2 = $sql2 . 'and classe_id = ' . $formdata->classe_id;
                }   
            
                if ($formdata->mes_ref)
                {
                    $sql2 = $sql2 . 'and mes_ref = ' . $formdata->mes_ref;
                }
            
                if ($formdata->unidade_id)
                {
                    $sql2 = $sql2 . ' and unidade_id = ' . $formdata->unidade_id;
                }
            
                $sql2 = $sql2 . " group by classe_id";
                
                $colunas2 = $conn2->query($sql2);
                
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('TOTAL ORIGINAL POR CLASSE', 'center', 'normal', 11);
                $tr->addRow();
                $tr->addCell('Classe', 'center', 'normal', 6);
                $tr->addCell('Valor Original', 'center', 'normal', 3);
                $tr->addCell('Valor Pago', 'center', 'normal', 2);
                
                $tot_normal = 0;
                $tot_pago = 0;  
                              
                foreach ($colunas2 as $object2) // feito pelo select
                {
                  $tr->addRow();
                  $tr->addCell($object2['descricao'], 'center', 'normal', 6);
                  $tr->addCell(number_format($object2['valor'], 2, ',', '.'), 'right', 'normal', 3);
                  $tr->addCell(number_format($object2['valor_pago'], 2, ',', '.'), 'right', 'normal', 2);
                  
                  $tot_normal += $object2['valor'];
                  $tot_pago += $object2['valor_pago'];
                
                }

                $tr->addRow();
                $tr->addCell('TOTAL', 'center', 'normal', 6);
                $tr->addCell(number_format($tot_normal, 2, ',', '.'), 'right', 'normal', 3);
                $tr->addCell(number_format($tot_pago, 2, ',', '.'), 'right', 'normal', 2);

                //////////////////////////////// fim totalizacao por conta classificacao //////////
                             
                $tr->addRow();
                   
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 11);
                
                // stores the file
                if (!file_exists("app/output/ContasReceberRelDataPag.{$format}") OR is_writable("app/output/ContasReceberRelDataPag.{$format}"))
                {
                    $tr->save("app/output/ContasReceberRelDataPag.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberRelDataPag.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberRelDataPag.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrado.');
            }
    
            $formdata->dt_pag_inicio = $string->formatDateBR($formdata->dt_pag_inicio);
            $formdata->dt_pag_fim = $string->formatDateBR($formdata->dt_pag_fim);
            
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
