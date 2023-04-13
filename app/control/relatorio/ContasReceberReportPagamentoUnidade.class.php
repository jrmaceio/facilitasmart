<?php
/**
 * ContasReceberReportPagamentoUnidade Report
 * @author  <your name here>
 * pagamento individual por unidade, crédito por unidade
 */
class ContasReceberReportPagamentoUnidade extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber_report');
        $this->form->setFormTitle('ContasReceber Report');
        
        // create the form fields
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
        
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Classe') ], [ $classe_id ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        $output_type->setSize('100%');


        
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF', 'xls' => 'XLS'));
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
        // add the action button
        $btn = $this->form->addAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
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
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('ContasReceber');
            $criteria   = new TCriteria;
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('situacao', '=', '1')); 
            $criteria->add(new TFilter('unidade_id', '!=', '0')); 
            
            if ($data->classe_id)
            {
                $criteria->add(new TFilter('classe_id', '=', "{$data->classe_id}"));
            }
            if ($data->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$data->unidade_id}"));
            }

           
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'unidade_id, dt_pagamento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(70,70,230,100,100,100,100,100,100,100);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths, $orientation='L');
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                $tr->addStyle('cabecalho', 'Arial', '10', 'B',   '#000000', '#ffffff');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - Gestão Condominial', 'center', 'cabecalho', 10);
                //$tr->getNativeWriter();
                $tr->addRow();
                
                $tr->addCell(utf8_decode(TSession::getValue('resumo')), 'center', 'header', 10);
                
                $tr->addRow();
                $tr->addCell(utf8_decode('Receita por Unidade'), 'center', 'header', 10);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'title');
                //$tr->addCell('Condominio Id', 'right', 'title');
                $tr->addCell('Mes Ref', 'center', 'title');
                $tr->addCell('Classe', 'right', 'title');
               // $tr->addCell('Unidade', 'right', 'title');
                $tr->addCell('Vencimento', 'center', 'title');
                $tr->addCell('Valor', 'right', 'title');
                 $tr->addCell('Desconto', 'right', 'title');
                //$tr->addCell('Descricao', 'left', 'title');
                //$tr->addCell('Situacao', 'left', 'title');
                $tr->addCell('Dt Pag', 'center', 'title');
                $tr->addCell('Dt Liq', 'center', 'title');
                //$tr->addCell('Conta Fechamento Id', 'right', 'title');
               
                $tr->addCell('Vlr Pago', 'right', 'title');
                $tr->addCell('Vlr Cred', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                $unidade = '';
                
                // data rows
                foreach ($objects as $object)
                {
                    if ($unidade != $object->unidade_id ){
                        // totalza a unidades
                        if ( $total_valor_pago > 0 ) {
                            $tr->addRow();
                            $tr->addCell('Total da unidade :', 'center', 'footer', 3);
                            $tr->addCell(number_format($total_valor_pago, 2, ',', '.'), 'right', 'footer', 4);
                            $tr->addCell(number_format($total_valor_creditado, 2, ',', '.'), 'right', 'footer');
                            
                            // zera totalizadores
                            $total_valor_pago = 0;
                            $total_valor_creditado = 0;
                    
                        }
                        
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                        
                        $unidade = new Unidade($object->unidade_id);
                        $proprietario = new Pessoa($unidade->proprietario_id);

                        $tr->addRow();
                        $tr->addCell($unidade->bloco_quadra . ' ' . $unidade->descricao .' - '. $proprietario->nome, 'left', 'normal', 9);
                        
                        $unidade = $object->unidade_id;
                    }
                        
                    $PlanoConta = new PlanoContas($object->classe_id);
                    $conta = $PlanoConta->descricao;
                    
                   
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    //$tr->addCell($object->condominio_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    $tr->addCell($conta, 'right', $style);
                   // $tr->addCell($descricao, 'right', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->desconto, 2, ',', '.'), 'right', $style);
                    //$tr->addCell($object->descricao, 'left', $style);
                    //$tr->addCell($object->situacao, 'left', $style);
                    $tr->addCell($string->formatDateBR($object->dt_pagamento), 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_liquidacao), 'center', $style);
                    //$tr->addCell($object->conta_fechamento_id, 'right', $style);
                    
                    $tr->addCell(number_format($object->valor_pago, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->valor_creditado, 2, ',', '.'), 'right', $style);

                    
                    $colour = !$colour;
                    
                    $total_valor_pago += $object->valor_pago;
                    $total_valor_creditado += $object->valor_creditado;
                    
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 10);
                
                // stores the file
                if (!file_exists("app/output/ContasReceber.{$format}") OR is_writable("app/output/ContasReceber.{$format}"))
                {
                    $tr->save("app/output/ContasReceber.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceber.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceber.{$format}");
                
                // shows the success message
                new TMessage('info', 'Report generated. Please, enable popups.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
