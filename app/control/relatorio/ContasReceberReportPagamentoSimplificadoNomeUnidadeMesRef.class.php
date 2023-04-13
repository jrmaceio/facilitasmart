<?php
/**
 * ContasReceberReportInadim Report
 * @author  <your name here>
 */
class ContasReceberReportPagamentoSimplificadoNomeUnidadeMesRef extends TPage
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
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber_report');
        $this->form->setFormTitle('Contas a Receber Liquidadas');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', '{proprietario_nome}', 'descricao', $criteria);
        
        //$dt_pagamento =new TDate('dt_pagamento');
        //$dt_pagamento->setMask('dd/mm/yyyy');
        $dt_pag_inicio =new TDate('dt_pag_inicio');
        $dt_pag_inicio->setMask('dd/mm/yyyy');
        $dt_pag_fim =new TDate('dt_pag_fim');
        $dt_pag_fim->setMask('dd/mm/yyyy');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        //$situacao = new TEntry('situacao');
        $mes_ref = new TEntry('mes_ref');
       
        
        $dt_lancamento = new TEntry('dt_lancamento');
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $valor = new TEntry('valor');
        $output_type = new TRadioGroup('output_type');

        //$situacao->setValue('0'); // em aberto 
        
        $classe_id->setSize('100%');
        //$situacao->setSize(50);
        $mes_ref->setSize('50%');
        $cobranca->setSize('50%');
        $unidade_id->setSize('100%');
        
        //$dt_liquidacao->setSize(100);
        $dt_pag_inicio->setSize('100%');
        $dt_pag_fim->setSize('100%');
        
        $tipo_lancamento->setSize('50%');

        
        // add the fields
               
        $this->form->addFields( [new TLabel('Classe')], [$classe_id],
                                [new TLabel('Mês Ref.')], [$mes_ref]                                
                            );

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id],
                                [new TLabel('Cobrança')], [$cobranca]                                
                            );
        
        $this->form->addFields( [new TLabel('Dt. Liquidação Inicial')], [$dt_pag_inicio],
                                [new TLabel('Dt. Liquidação Final')], [$dt_pag_fim]                                
                            );
        
        $change_data = new TAction(array($this, 'onChangeData'));
        $dt_pag_inicio->setExitAction($change_data);
        $dt_pag_fim->setExitAction($change_data);

        $this->form->addFields( [new TLabel('Output')], [$output_type]);
 
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        // add the action button
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

            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');

            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('ContasReceber');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'mes_ref';
                $param['direction'] = 'asc';
            }
            
            $formdata->dt_pag_inicio = $string->formatDate($formdata->dt_pag_inicio);
            $formdata->dt_pag_fim = $string->formatDate($formdata->dt_pag_fim);
            
            $criteria->setProperties($param); // order, offset
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            if ($formdata->cobranca)
            {
                $criteria->add(new TFilter('cobranca', 'like', "%{$formdata->cobranca}%"));
            }
    
            if ($formdata->classe_id)
            {
                $criteria->add(new TFilter('classe_id', 'like', "%{$formdata->classe_id}%"));
            }
            
            // titulos pagos
            $criteria->add(new TFilter('situacao', '=', "1"));
            
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', 'like', "%{$formdata->mes_ref}%"));
            }
            
            if ($formdata->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$formdata->unidade_id}"));
            }
            
            if ($formdata->dt_pag_inicio)
            {
                $criteria->add(new TFilter('dt_liquidacao', 'between', $formdata->dt_pag_inicio, $formdata->dt_pag_fim)); 
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
            
                       
            if ($objects)
            {
                //$widths = array(50,50,100,100,100,50,50,50,50,100,100,100,50,100,100,100,100,100);
                $widths = array(30,45,250,150,60,60,60,60,60,50);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths, $orientation='L');
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths, $orientation='L');
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '8', 'I',  '#000000', '#A3A3A3');
                
                $tr->addRow();
                $tr->addCell('Relação de recebimentos por data de crédito por mês de referência - Período de : ' . $string->formatDateBR($formdata->dt_pag_inicio) . ' até ' . $string->formatDateBR($formdata->dt_pag_fim), 'center', 'header', 10);
                
                // add a header row
                $tr->addRow();
                $tr->addCell(TSession::getValue('resumo'), 'center', 'header', 10);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'cabecalho');
                //// cabecalho /////////////////$tr->addCell('Imovel Id', 'right', 'title');
                $tr->addCell('Mes Ref', 'center', 'cabecalho');
                $tr->addCell('Pagador', 'center', 'cabecalho');
                //$tr->addCell('Tipo Lancamento', 'left', 'title');
                $tr->addCell('Classe', 'left', 'cabecalho');
                //// cabecalho //////////////////////$tr->addCell('Unidade Id', 'right', 'title');
                //$tr->addCell('Dt Lancamento', 'left', 'title');
                $tr->addCell('Dt Vencimento', 'center', 'cabecalho');
                $tr->addCell('Dt Pagamento', 'center', 'title');
                $tr->addCell('Dt Crédito', 'center', 'title');
                $tr->addCell('Vlr Pago', 'right', 'cabecalho');
                
                $tr->addCell('Vlr Creditado', 'right', 'cabecalho');
                $tr->addCell('Nosso Núm.', 'right', 'cabecalho');
               
                // controls the background filling
                $colour= FALSE;
                
                $total_valor_creditado = 0;
                $total_valor_pago = 0;
                
                $total_geral_valor_creditado = 0;
                $total_geral_valor_pago = 0;

                $inicio = 0;
                $mes_ref = '';
                
                $qtd_titulos = 0;
                $qtd_titulos_geral = 0;
                
                // data rows
                foreach ($objects as $object)
                {
                    if ($inicio == 0) {
                        $mes_ref = $object->mes_ref;
                        $inicio = 1;
                    }
                    
                    if ($mes_ref != $object->mes_ref and $inicio != 0) {
                        $tr->addRow();
                        $tr->addCell('Total ' . $mes_ref . ' ('.$qtd_titulos.' registro(s))', 'right', 'footer', 7);
                        $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                        $tr->addCell(number_format($total_valor_creditado, 2, ',', '.'), 'right', 'footer');
                        $tr->addCell('', 'right', 'footer');
                        
                        $total_valor_creditado = 0;
                        $total_valor_pago = 0;
                        $qtd_titulos = 0;
                        $mes_ref = $object->mes_ref;                        
                    
                    }       
                                                 
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    ////////////$tr->addCell($object->imovel_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    
                    $unidade = new Unidade($object->unidade_id);
                    $descricao = $unidade->descricao;

                    $pessoa = new Pessoa($unidade->proprietario_id);
                    $proprietario = $pessoa->nome;

                    $tr->addCell($descricao.'-'.$proprietario, 'left', $style);
                        
                    //$tr->addCell($object->tipo_lancamento, 'left', $style);
                    
                    $PlanoConta = new PlanoContas($object->classe_id);
                    $conta = $PlanoConta->descricao;

                    $tr->addCell($conta, 'left', $style);
                    
                    ///////////////////$tr->addCell($object->unidade_id, 'right', $style);
                    //$tr->addCell($object->dt_lancamento, 'left', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_pagamento), 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_liquidacao), 'center', $style);
                    $tr->addCell(number_format($object->valor_pago, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->valor_creditado, 2, ',', '.'), 'right', $style);
                    $tr->addCell($object->nosso_numero, 'right', $style);
                    
                    $total_valor_creditado += $object->valor_creditado;
                    $total_valor_pago += $object->valor_pago;
                    
                    $total_geral_valor_creditado += $object->valor_creditado;
                    $total_geral_valor_pago += $object->valor_pago;
                    
                    $qtd_titulos++;
                    $qtd_titulos_geral++;
                    
                    //$tr->addCell($object->descricao, 'left', $style);
                    //$tr->addCell($object->situacao, 'left', $style);
                    
                    //$tr->addCell($object->valor_pago, 'left', $style);
                    //$tr->addCell($object->desconto, 'left', $style);
                    //$tr->addCell($object->juros, 'left', $style);
                    //$tr->addCell($object->multa, 'left', $style);
                    //$tr->addCell($object->correcao, 'left', $style);

                    
                    $colour = !$colour;
                }
                
                // total do ultimo mes_ref
                $tr->addRow();
                $tr->addCell('Total '  . $mes_ref . ' ('.$qtd_titulos.' registro(s))' , 'right', 'footer', 7);
                $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_valor_creditado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell('', 'right', 'footer');
                
                // footer row
                $tr->addRow();
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                $tr->addCell('Total geral:' . ' ('.$qtd_titulos_geral.' registro(s))', 'right', 'footer', 7);
                $tr->addCell(number_format($total_geral_valor_pago, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_valor_creditado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell('', 'right', 'footer');
                
                // footer row
                $tr->addRow();
                //$tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 18);
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 10);
                // stores the file
                if (!file_exists("app/output/ContasReceberReportPagamentoSimplificadoNomeUnidade.{$format}") OR is_writable("app/output/ContasReceberReportPagamentoSimplificadoNomeUnidade.{$format}"))
                {
                    $tr->save("app/output/ContasReceberReportPagamentoSimplificadoNomeUnidade.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberReportPagamentoSimplificadoNomeUnidade.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberReportPagamentoSimplificadoNomeUnidade.{$format}");
                
                // shows the success message
                new TMessage('info', 'Report generated. Please, enable popups.');
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
