<?php
/**
 * ContasReceberRelPagDataUnidNome
 * @author  <your name here>
 */
class ContasReceberRelPagDataUnidNome extends TPage
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
        $this->form = new TQuickForm('form_ContasReceber_report');
        $this->form->class = 'tform'; // change CSS class
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Recebimentos por Data');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');
        $unidade_id = new TEntry('unidade_id');
        
        //$dt_pagamento =new TDate('dt_pagamento');
        //$dt_pagamento->setMask('dd/mm/yyyy');
        $dt_pag_inicio =new TDate('dt_pag_inicio');
        $dt_pag_inicio->setMask('dd/mm/yyyy');
        $dt_pag_fim =new TDate('dt_pag_fim');
        $dt_pag_fim->setMask('dd/mm/yyyy');
        
        //$classe_id = new TEntry('classe_id');
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
        
        $classe_id->setSize(50);
        //$situacao->setSize(50);
        $mes_ref->setSize(100);
        $cobranca->setSize(50);
        $unidade_id->setSize(50);
        
        //$dt_pagamento->setSize(100);
        $dt_pag_inicio->setSize(100);
        $dt_pag_fim->setSize(100);
        
        $tipo_lancamento->setSize(50);
        
        // add the fields
        $this->form->addQuickFields('Classe Id:', array($classe_id, 
        new TLabel('Mês Referência:'),$mes_ref,
        new TLabel('Cobrança:'),$cobranca,
        new TLabel('Unidade Id:'),$unidade_id));
        
        $this->form->addQuickFields('Data pagamento início:', array($dt_pag_inicio, new TLabel('Fim:'),$dt_pag_fim));
        
        //$this->form->addQuickFields('Unidade Id:', array($unidade_id,  
        //new TLabel('Dt Pagamento:'),$dt_pagamento));
        
        //$this->form->addQuickField('Tipo Lancamento', $tipo_lancamento,  100 );
        //$this->form->addQuickField('Dt Lancamento', $dt_lancamento,  50 );
        //$this->form->addQuickField('Valor', $valor,  100 );
        
        $change_data = new TAction(array($this, 'onChangeData'));
        $dt_pag_inicio->setExitAction($change_data);
        $dt_pag_fim->setExitAction($change_data);

        $this->form->addQuickField('Output', $output_type,  100 , new TRequiredValidator);
 
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        // add the action button
        $this->form->addQuickAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        /////////////////////////////$container->add(TPanelGroup::pack('Relatório', $this->form));
        $container->add($this->form);
        
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
            TTransaction::open('facilita');
            
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
            $criteria->add(new TFilter('imovel_id', '=', TSession::getValue('id_imovel'))); // add the session filter

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
                $criteria->add(new TFilter('dt_pagamento', 'between', $formdata->dt_pag_inicio, $formdata->dt_pag_fim)); 
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
            
                       
            if ($objects)
            {
                $widths = array(30,45,55,80,70,50,60,40,40,60);
                
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
                
                $tr->addRow();
                $tr->addCell('Relação de Recebimentos por data - Data pagamento de : ' . $string->formatDateBR($formdata->dt_pag_inicio) . ' até ' . $string->formatDateBR($formdata->dt_pag_fim), 'center', 'header', 10);
                
                // add a header row
                $tr->addRow();
                $tr->addCell(TSession::getValue('resumo'), 'center', 'header', 10);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'cabecalho');
                //// cabecalho /////////////////$tr->addCell('Imovel Id', 'right', 'title');
                $tr->addCell('Mes Ref', 'left', 'cabecalho');
                
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Nome', 'center', 'cabecalho');
                $tr->addCell('Classe', 'left', 'cabecalho');
                //// cabecalho //////////////////////$tr->addCell('Unidade Id', 'right', 'title');
                //$tr->addCell('Dt Lancamento', 'left', 'title');
                $tr->addCell('Vencimento', 'center', 'cabecalho');
                
                $tr->addCell('Valor', 'right', 'cabecalho');
                
                $tr->addCell('Multa', 'right', 'cabecalho');
                $tr->addCell('Juros', 'right', 'cabecalho');
                $tr->addCell('Vlr Pago', 'right', 'cabecalho');
                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor_lancado = 0;
                $total_geral_valor_pago = 0;
                $total_geral_multa = 0;
                $total_geral_juros = 0;
                
                
                $total_valor_lancado = 0;
                $total_valor_pago = 0;
                $total_multa = 0;
                $total_juros = 0;
               
                $unidade = '';
                $datapagamento ='';
                
                       
                               
                // data rows
                foreach ($objects as $object)
                {
                    if (  $datapagamento == '' ) 
                    {
                        $datapagamento = $object->dt_pagamento;
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '8', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell('Data Pagamento : ' . $string->formatDateBR($datapagamento), 'left', 'normal', 10);
                    }
                    
                    //if ($unidade !=$object->unidade_id ){
                    if ($datapagamento != $object->dt_pagamento ){
                    
                        if ( $total_valor_lancado > 0 ) {
                            $tr->addRow();
                            $tr->addCell('Total :', 'center', 'footer', 6);
                            $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                            
                            // zera totalizadores
                            $total_valor_lancado = 0;
                            $total_valor_pago = 0;
                            $total_multa = 0;
                            $total_juros = 0;
                    
                        }
                    
                        //$unidade = $object->unidade_id;
                        $datapagamento = $object->dt_pagamento;
                    
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '8', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell('Data Pagamento : ' . $string->formatDateBR($datapagamento), 'left', 'normal', 10);
                        
                    }
                
                    $descricao = Unidades::RetornaDescricaoUnidade($object->unidade_id);
                    $proprietario = Unidades::RetornaProprietarioUnidade($object->unidade_id);
                                       
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    ////////////$tr->addCell($object->imovel_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    
                    $tr->addCell($descricao, 'center', $style);
                    $tr->addCell(substr($proprietario,0,70), 'left', $style);
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                    $tr->addCell($conta, 'left', $style);
                    
                    ///////////////////$tr->addCell($object->unidade_id, 'right', $style);
                    //$tr->addCell($object->dt_lancamento, 'left', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    
                                      
                    $tr->addCell(number_format($object->multa, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->juros, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->valor_pago, 2, ',', '.'), 'right', $style);
                    
                    $total_valor_lancado += $object->valor;
                    $total_valor_pago += $object->valor_pago;
                    $total_multa += $object->multa;
                    $total_juros += $object->juros;
                    
                    $total_geral_valor_lancado += $object->valor;
                    $total_geral_valor_pago += $object->valor_pago;
                    $total_geral_multa += $object->multa;
                    $total_geral_juros += $object->juros;
                                        
                    //$tr->addCell($object->descricao, 'left', $style);
                    //$tr->addCell($object->situacao, 'left', $style);
                    
                    //$tr->addCell($object->valor_pago, 'left', $style);
                    //$tr->addCell($object->desconto, 'left', $style);
                    //$tr->addCell($object->juros, 'left', $style);
                    //$tr->addCell($object->multa, 'left', $style);
                    //$tr->addCell($object->correcao, 'left', $style);

                    
                    $colour = !$colour;
                }
                
                // totaliza ultimo dia impresso
                $tr->addRow();
                $tr->addCell('Total :', 'center', 'footer', 6);
                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer');
                
                // footer row
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 10);
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total Geral:', 'center', 'footer', 6);
                $tr->addCell(number_format($total_geral_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_multa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_juros, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_valor_pago, 2, ',', '.'), 'right', 'footer');
                
                // footer row
                $tr->addRow();
                //$tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 18);
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 10);
                // stores the file
                if (!file_exists("app/output/ContasReceberInadimp.{$format}") OR is_writable("app/output/ContasReceberInadimp.{$format}"))
                {
                    $tr->save("app/output/ContasReceberInadimp.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberInadimp.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberInadimp.{$format}");
                
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
