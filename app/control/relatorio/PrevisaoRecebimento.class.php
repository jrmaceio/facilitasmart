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
class PrevisaoRecebimento extends TPage
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
        $this->form = new BootstrapFormBuilder('form_teste_report');
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
            $query = '   SELECT 
          contas_receber.classe_id as "classe_id",
          sum(contas_receber.valor - contas_receber.desconto_boleto_cobranca) as "previsto",
          sum(contas_receber.valor_pago) as "pago"
     FROM contas_receber
     where condominio_id = :condominio and mes_ref = :mesref
     group by classe_id
';
            
            $filters = [];
            $filters['condominio'] = TSession::getValue('id_condominio');
            $filters['mesref'] = TSession::getValue('mesref');
           
            $data = TDatabase::getData($source, $query, null, $filters );
            
            if ($data)
            {
                $widths = [200, 100, 100, 100];
                
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
                    $table->addStyle('header', 'Helvetica', '16', 'B', '#ffffff', '#4B8E57');
                    $table->addStyle('title',  'Helvetica', '10', 'B', '#ffffff', '#6CC361');
                    $table->addStyle('datap',  'Helvetica', '10', '',  '#000000', '#E3E3E3', 'LR');
                    $table->addStyle('datai',  'Helvetica', '10', '',  '#000000', '#ffffff', 'LR');
                    $table->addStyle('footer', 'Helvetica', '10', '',  '#2B2B2B', '#B5FFB4');
                    
                    $condominio = new Condominio(TSession::getValue('id_condominio'));
                    //var_dump($condominio);
                    
                    $table->setHeaderCallback( function($table) {
                        $table->addRow();
                        $table->addCell('Mapa Financeiro Atual', 'center', 'header', 4);
                        $table->addRow();
                        $table->addCell($condominio['resumo'] . ' - '. TSession::getValue('mesref'), 'center', 'header', 4);
                        
                        $table->addRow();
                        $table->addCell('Classe', 'center', 'title');
                        $table->addCell('Previsto', 'center', 'title');
                        $table->addCell('Recebido', 'center', 'title');
                        $table->addCell('Inadimpência', 'center', 'title');
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
                        
                        $classe = new PlanoContas($row['classe_id']);
                        
                        $table->addRow();
                        $table->addCell($classe->descricao, 'center', $style);
                        $table->addCell(number_format($row['previsto'], 2, ',', '.'), 'center', $style);
                        $table->addCell(number_format($row['pago'], 2, ',', '.'), 'center', $style);
                        
                        if ($row['previsto']> 0) {
                            $recebido = (float)$row['pago'];
                            $previsto = (float)$row['previsto'];
                            $taxa = ($recebido/$previsto);
                            $taxa = (1 - $taxa) * 100;
                            $table->addCell(number_format($taxa, 2, ',', '.') .  ' %', 'center', $style);
                        } else {    
                            $table->addCell(0, 'center', $style);
                        }
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
