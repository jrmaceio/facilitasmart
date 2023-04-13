<?php
/**
 * Tabular Query Report
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class chaves_pjbank extends TPage
{
    private $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_chaves_pjbank_report');
        $this->form->setFormTitle( 'Report' );
        
        // create the form fields
        

        



        
        $output_type  = new TRadioGroup('output_type');
        $this->form->addFields( [new TLabel('Output')],   [$output_type] );
        
        // define field properties
        $output_type->setUseButton();
        $options = ['html' =>'HTML', 'pdf' =>'PDF', 'rtf' =>'RTF', 'xls' =>'XLS'];
        $output_type->addItems($options);
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        $this->form->addAction( 'Generate', new TAction(array($this, 'onGenerate')), 'fa:download blue');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        
        parent::add($vbox);
    }

    /**
     * method onGenerate()
     * Executed whenever the user clicks at the generate button
     */
    function onGenerate()
    {
        try
        {
            // get the form data into an active record Customer
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $format = $data->output_type;
            
            // open a transaction with database 'facilitasmart'
            $source = TTransaction::open('facilitasmart');
            
            // define the query
            $query = '   SELECT condominio.id as "id",
          condominio.resumo as "resumo",
          condominio.credencial_pjbank as "credencial_pjbank",
          condominio.chave_pjbank as "chave_pjbank",
          condominio.chave_cd_pjbank as "chave_cd_pjbank"
     FROM condominio order by chave_pjbank 
';
            
            $filters = [];

            
            $data = TDatabase::getData($source, $query, null, $filters );
            
            if ($data)
            {
                $widths = [50,300,400, 400];
                
                switch ($format)
                {
                    case 'html':
                        $table = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $table = new TTableWriterPDF($widths);
                        break;
                    case 'rtf':
                        $table = new TTableWriterRTF($widths);
                        break;
                    case 'xls':
                        $table = new TTableWriterXLS($widths);
                        break;
                }
                
                if (!empty($table))
                {
                    // create the document styles
                    $table->addStyle('header', 'Helvetica', '14', 'B', '#ffffff', '#4B8E57');
                    $table->addStyle('title',  'Helvetica', '8', 'B', '#ffffff', '#6CC361');
                    $table->addStyle('datap',  'Helvetica', '6', '',  '#000000', '#E3E3E3', 'LR');
                    $table->addStyle('datai',  'Helvetica', '6', '',  '#000000', '#ffffff', 'LR');
                    $table->addStyle('footer', 'Helvetica', '8', '',  '#2B2B2B', '#B5FFB4');
                    
                    $table->setHeaderCallback( function($table) {
                        $table->addRow();
                        $table->addCell('Chaves PJBank', 'center', 'header', 4);
                        
                        $table->addRow();
                        $table->addCell('Id', 'center', 'title');
                        $table->addCell('Resumo', 'center', 'title');
                        //$table->addCell('Credencial', 'center', 'title');
                        $table->addCell('Chave Boleto', 'center', 'title');
                        $table->addCell('Chave Conta Digital', 'center', 'title');
                    });
                    
                    $table->setFooterCallback( function($table) {
                        $table->addRow();
                        $table->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 4);
                    });
                    
                    // controls the background filling
                    $colour= FALSE;
                    
                    // data rows
                    foreach ($data as $row)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        
                        $table->addRow();
                        $table->addCell($row['id'], 'center', $style);
                        $table->addCell($row['resumo'], 'center', $style);
                        //$table->addCell($row['credencial_pjbank'], 'center', $style);
                        $table->addCell($row['chave_pjbank'], 'center', $style);
                        $table->addCell($row['chave_cd_pjbank'], 'center', $style);
                        
                        $colour = !$colour;
                    }
                    
                    $output = "app/output/tabular.{$format}";
                    
                    // stores the file
                    if (!file_exists($output) OR is_writable($output))
                    {
                        $table->save($output);
                        parent::openFile($output);
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . $output);
                    }
                    
                    // shows the success message
                    new TMessage('info', 'Report generated. Please, enable popups in the browser.');
                }
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
