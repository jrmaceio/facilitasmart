<?php
/**
 * ContasReceberReportConfBoleto Report
 * @author  <your name here>
 */
class ContasReceberReportConfBoleto extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
            
            parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber_report');
        $this->form->setFormTitle('ContasReceber Report');
        

        // create the form fields
        $condominio_id = new TEntry('condominio_id');
        $mes_ref = new TEntry('mes_ref');
        $cobranca = new TEntry('cobranca');
        $classe_id = new TEntry('classe_id');
        $unidade_id = new TEntry('unidade_id');
        $situacao = new TEntry('situacao');
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Condominio') ], [ $condominio_id ] );
        $this->form->addFields( [ new TLabel('Mes Ref') ], [ $mes_ref ] );
        $this->form->addFields( [ new TLabel('Cobranca') ], [ $cobranca ] );
        $this->form->addFields( [ new TLabel('Classe') ], [ $classe_id ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('Situacao') ], [ $situacao ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $condominio_id->setSize('100%');
        $mes_ref->setSize('100%');
        $cobranca->setSize('100%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        $situacao->setSize('100%');
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
            
            if ($data->condominio_id)
            {
                $criteria->add(new TFilter('condominio_id', '=', "{$data->condominio_id}"));
            }
            if ($data->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$data->mes_ref}"));
            }
            if ($data->cobranca)
            {
                $criteria->add(new TFilter('cobranca', '=', "{$data->cobranca}"));
            }
            if ($data->classe_id)
            {
                $criteria->add(new TFilter('classe_id', '=', "{$data->classe_id}"));
            }
            if ($data->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$data->unidade_id}"));
            }
            if ($data->situacao)
            {
                $criteria->add(new TFilter('situacao', '=', "{$data->situacao}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(70,70,100,70,70,100,120,100,100, 100, 100, 100, 100, 100, 100, 70);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
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
                
                $condominio = new Condominio( TSession::getValue('id_condominio'));
                
                // add a header row
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', 16);
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Conferência na emissão de boletos', 'center', 'header', 16);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'title');
                $tr->addCell('Cond.', 'right', 'title');
                $tr->addCell('Mes Ref', 'center', 'title');
                $tr->addCell('Cob', 'center', 'title');
                $tr->addCell('Classe', 'center', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Vencimento', 'center', 'title');
                $tr->addCell('Valor', 'right', 'title');
                
                $tr->addCell('Multa', 'right', 'title');
                $tr->addCell('Juros', 'right', 'title');
                $tr->addCell('Desconto', 'right', 'title');
                $tr->addCell('Dt Desc.', 'right', 'title');
                
                $tr->addCell('Prev.Rec.', 'right', 'title');
                $tr->addCell('Vlr Pago', 'right', 'title');
                $tr->addCell('Dt. Pag.', 'right', 'title');
                $tr->addCell('Sit.', 'center', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                $total = 0.00;
                $previsao_recebimento = 0;
                $pago = 0;
                
                // data rows
                foreach ($objects as $object)
                {
                    $unidade = new Unidade($object->unidade_id);
                    
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    $tr->addCell($object->condominio_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    $tr->addCell($object->cobranca, 'center', $style);
                    $tr->addCell($object->classe_id, 'center', $style);
                    $tr->addCell($unidade->bloco_quadra . '-' . $unidade->descricao, 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    
                    $tr->addCell(number_format($object->multa_boleto_cobranca, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->juros_boleto_cobranca, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->desconto_boleto_cobranca, 2, ',', '.'), 'right', $style);
                    $tr->addCell($string->formatDateBR($object->dt_limite_desconto_boleto_cobranca), 'center', $style);
                    
                    $tr->addCell(number_format($object->valor-$object->desconto_boleto_cobranca, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($object->valor_pago, 2, ',', '.'), 'right', $style);
                    $tr->addCell($string->formatDateBR($object->dt_liquidacao), 'center', $style);
                    $tr->addCell($object->situacao, 'center', $style);

                    
                    $colour = !$colour;
                    
                    $previsao_recebimento += $object->valor-$object->desconto_boleto_cobranca;
                    $pago += $object->valor_pago;
                    $total += $object->valor;
                }
                
                $tr->addRow();
                $tr->addCell('Total', 'right', 'footer', 7);
                $tr->addCell(number_format($total, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                $tr->addCell(number_format($previsao_recebimento, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($pago, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                $tr->addCell(' ', 'right', 'footer');
                
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 16);
                
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
