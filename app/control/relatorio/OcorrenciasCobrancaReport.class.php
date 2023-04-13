<?php
/**
 * VwContasreceberlistcobrancaReport Report
 * @author  <your name here>
 */
class OcorrenciasCobrancaReport extends TPage
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
        $this->form = new BootstrapFormBuilder('form_VwContasreceberlistcobranca_report');
        $this->form->setFormTitle('Relatório de Ocorrências de Cobrança');
        

        // create the form fields
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);

        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Unidade') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
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
            
            $repository = new TRepository('VwContasreceberlistcobranca');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            
            if ($data->id)
            {
                $criteria->add(new TFilter('id', '=', "{$data->id}"));
            }

            // somente um imovel selecionado
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
 
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(50,80,570,100);
                
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
                $tr->addStyle('datap', 'Arial', '8', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '8', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Relatório de cobrança', 'center', 'header', 6);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Nome', 'left', 'title');
                $tr->addCell('', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    
                    
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    $tr->addCell($object->descricao, 'center', $style);
                    $tr->addCell($object->nome, 'left', $style);

                    $tr->addCell('', 'left', $style);
                    //$tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);

                    //$ocorrencias = new OcorrenciaUnidade($object->id);
                    $conn = TTransaction::get();
                    $sql = "SELECT * 
                            FROM ocorrencia_unidade 
                            where 
                            unidade_id = " . $object->id;
                        
                    $colunas = $conn->query($sql);
                    
                    foreach ($colunas as $coluna)
                    {
                        
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($coluna['data_ocorrencia']), 'right', $style, 2);
                        //$tr->addCell($coluna['descricao'], 'left', $style, 3);
                        // quebra de linha
                        //$tr->addRow();
                        $fpdf = $tr->getNativeWriter();
                        $fpdf->multicell(663, 18, utf8_decode($coluna['descricao']),1,'L', false);
                    }
            
                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 6);
                
                // stores the file
                if (!file_exists("app/output/VwContasreceberlistcobranca.{$format}") OR is_writable("app/output/VwContasreceberlistcobranca.{$format}"))
                {
                    $tr->save("app/output/VwContasreceberlistcobranca.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/VwContasreceberlistcobranca.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/VwContasreceberlistcobranca.{$format}");
                
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
