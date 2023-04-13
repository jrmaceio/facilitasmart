<?php
/**
 * BancoReport Report
 * @author  <your name here>
 */
class BancoReport extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Banco_report');
        $this->form->setFormTitle('Banco Report');
        
        // create the form fields
        $id = new TEntry('id');

        $codigo_bacen = new TEntry('codigo_bacen');
        $sigla = new TEntry('sigla');
        $descricao = new TEntry('descricao');
        $status = new TCombo('status');
        $status->addItems(array('A' => 'Ativo', 'I' => 'Inativo'));

        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Codigo Bacen') ], [ $codigo_bacen ] );
        $this->form->addFields( [ new TLabel('Sigla') ], [ $sigla ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $codigo_bacen->setSize('100%');
        $sigla->setSize('100%');
        $descricao->setSize('100%');
        $status->setSize('100%');
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

        $status_obj = 'A';
                       
        $obj = new StdClass;
        $obj->status = $status_obj;
        
        TForm::sendData('form_Banco_report', $obj);

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
            
            $repository = new TRepository('Banco');
            $criteria   = new TCriteria;

            if ($data->id)
            {
                $criteria->add(new TFilter('id', '=', "{$data->id}"));
            }
            if ($data->codigo_bacen)
            {
                $criteria->add(new TFilter('codigo_bacen', 'like', "%{$data->codigo_bacen}%"));
            }
            if ($data->sigla)
            {
                $criteria->add(new TFilter('sigla', 'like', "%{$data->sigla}%"));
            }
            if ($data->descricao)
            {
                $criteria->add(new TFilter('descricao', 'like', "%{$data->descricao}%"));
            }
            if ($data->status)
            {
                $criteria->add(new TFilter('status', '=', "$data->status"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(20,60,200,500,40);
                
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
                $tr->addCell('Banco', 'center', 'header', 5);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'title');
                $tr->addCell('C.Bacen', 'left', 'title');
                $tr->addCell('Sigla', 'center', 'title');
                $tr->addCell('Descricao', 'left', 'title');
                $tr->addCell('St', 'center', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    $tr->addCell($object->codigo_bacen, 'left', $style);
                    $tr->addCell($object->sigla, 'center', $style);
                    $tr->addCell($object->descricao, 'left', $style);
                    $tr->addCell($object->status, 'center', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 5);
                
                // stores the file
                if (!file_exists("app/output/Banco.{$format}") OR is_writable("app/output/Banco.{$format}"))
                {
                    $tr->save("app/output/Banco.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Banco.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Banco.{$format}");
                
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
