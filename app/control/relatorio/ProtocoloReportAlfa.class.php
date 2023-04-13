<?php
/**
 * ProtocoloReportAlfa Report
 * @author  <your name here>
 */
class ProtocoloReportAlfa extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ProtocoloAlfa_report');
        $this->form->setFormTitle('Protolo ordem alfabética');
        

        // create the form fields
        $texto1 = new TEntry('texto1');
        $texto2 = new TEntry('texto2');
        $bloco_quadra = new TEntry('bloco_quadra');
        $descricao = new TEntry('descricao');
        $nome = new TEntry('nome');
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Bloco Quadra') ], [ $bloco_quadra ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Texto 1') ], [ $texto1 ] );
        $this->form->addFields( [ new TLabel('Texto 2') ], [ $texto2 ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $bloco_quadra->setSize('100%');
        $descricao->setSize('100%');
        $nome->setSize('100%');
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
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('Listagerarboletos');
            $criteria   = new TCriteria;
            
            // somente um condominio selecionado em mes referencia 
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
         
         
            if ($data->bloco_quadra)
            {
                $criteria->add(new TFilter('bloco_quadra', '=', "{$data->bloco_quadra}"));
            }
            if ($data->descricao)
            {
                $criteria->add(new TFilter('descricao', '=', "{$data->descricao}"));
            }
            if ($data->nome)
            {
                $criteria->add(new TFilter('nome', 'like', "%{$data->nome}%"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(50,50,150,100,200);
                
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
                $tr->addStyle('datap', 'Arial', '9', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '9', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell($data->texto1, 'center', 'header', 5);
                
                $tr->addRow();
                $tr->addCell($data->texto2, 'center', 'title', 5);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Bl/Qd', 'center', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Nome', 'center', 'title');
                $tr->addCell('Data', 'center', 'title');
                $tr->addCell('Assinatura', 'center', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->bloco_quadra, 'center', $style);
                    $tr->addCell($object->descricao, 'center', $style);
                    $tr->addCell($object->nome, 'left', $style);
                    $tr->addCell('', 'right', $style);
                    $tr->addCell('', 'left', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 5);
                
                // stores the file
                if (!file_exists("app/output/ProtocoloAlfa.{$format}") OR is_writable("app/output/ProtocoloAlfa.{$format}"))
                {
                    $tr->save("app/output/ProtocoloAlfa.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ProtocoloAlfa.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ProtocoloAlfa.{$format}");
                
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
