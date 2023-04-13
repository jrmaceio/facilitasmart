<?php
/**
 * ChequeReport Report
 * @author  <your name here>
 */
class ChequesEmitidos extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Cheque_report');
        $this->form->setFormTitle('Cheque Report');
        

        // create the form fields
        $id = new TEntry('id');
        $documento = new TEntry('documento');
        
        $mes_ref = new TEntry('mes_ref');
        
        //$dt_vencimento = new TEntry('dt_vencimento');
        //$cheque = new TDBUniqueSearch('cheque', 'facilitasmart', 'Cheque', 'id', 'documento');
        
        $output_type = new TRadioGroup('output_type');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Documento') ], [ $documento ] );
        $this->form->addFields( [ new TLabel('Mês Referência') ], [ $mes_ref ] );
        //$this->form->addFields( [ new TLabel('Dt Vencimento') ], [ $dt_vencimento ] );
        //$this->form->addFields( [ new TLabel('Cheque') ], [ $cheque ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $documento->setSize('100%');
        $mes_ref->setSize('100%');
        //$dt_vencimento->setSize('100%');
        //$cheque->setSize('100%');
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
        $container->style = 'width: 90%';
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
            
            $repository = new TRepository('Cheque');
            $criteria   = new TCriteria;
            
            if ($data->id)
            {
                $criteria->add(new TFilter('id', '=', "{$data->id}"));
            }
            
            if ($data->documento)
            {
                $criteria->add(new TFilter('documento', '=', "{$data->documento}"));
            }
            
            if ($data->mes_ref)
            {
                $criteria->add(new TFilter('mes_referencia', '=', "{$data->mes_ref}"));
            }
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
            
            //if ($data->dt_vencimento)
            //{
            //    $criteria->add(new TFilter('dt_vencimento', 'like', "%{$data->dt_vencimento}%"));
            //}
            
            //if ($data->cheque)
            //{
            //    $criteria->add(new TFilter('cheque', '=', "{$data->cheque}"));
            //}

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(50,100,100,100,100,100);
                
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
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Cheques Emitidos', 'center', 'header', 6);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'title');
                $tr->addCell('Documento', 'center', 'title');
                $tr->addCell('Dt Emissão', 'center', 'title');
                $tr->addCell('Dt Vencimento', 'center', 'title');
                $tr->addCell('Cheque', 'center', 'title');
                $tr->addCell('Valor', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;

                $total = 0;
                                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    $tr->addCell($object->documento, 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_emissao), 'center', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell($object->cheque, 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);

                    $total += $object->valor;
                    
                    $colour = !$colour;
                }
                
                $tr->addRow();
                $tr->addCell('Total R$ ' . number_format($total, 2, ',', '.'), 'right', 'footer', 6);
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 6);
                
                // stores the file
                if (!file_exists("app/output/ChequeEmitidos.{$format}") OR is_writable("app/output/ChequeEmitidos.{$format}"))
                {
                    $tr->save("app/output/ChequeEmitidos.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ChequeEmitidos.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ChequeEmitidos.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado com sucesso. Por favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrato.');
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
