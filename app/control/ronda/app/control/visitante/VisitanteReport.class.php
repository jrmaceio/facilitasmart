<?php
/**
 * VisitanteReport Report
 * @author  <your name here>
 */
class VisitanteReport extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Visitante_report');
        $this->form->setFormTitle('Visitante Report');
        

        // create the form fields
        $status = new TCombo('status');
        
        $postofilter = new TCriteria;
        $postofilter->add(new TFilter('unidade_id', '=', TSession::getValue('userunitid')));
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao', 'descricao asc', $postofilter); 
        
        $output_type = new TRadioGroup('output_type');

        $status->addItems( [ 'Y' => 'Liberado', 'N' => 'Bloqueado' ] ); 
        
        // add the fields
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $status->setSize('100%');
        $posto_id->setSize('100%');
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
            // open a transaction with database 'ronda'
            TTransaction::open('ronda');
            
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('Visitante');
            $criteria   = new TCriteria;
            
            $param['order'] = 'posto_id, nome'; 
            $param['direction'] = 'asc';
            $criteria->setProperties($param);
            
            if ($data->status)
            {
                $criteria->add(new TFilter('status', 'like', "%{$data->status}%"));
            }
            if ($data->posto_id)
            {
                $criteria->add(new TFilter('posto_id', '=', "{$data->posto_id}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(100,500,150,350,400,500);
                
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
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Visitante Autorizados por Posto', 'center', 'header', 6);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'title');
                $tr->addCell('Nome', 'left', 'title');
                $tr->addCell('Status', 'left', 'title');
                $tr->addCell('Motivo/Função/Finalidade', 'left', 'title');
                $tr->addCell('Documento', 'left', 'title');
                $tr->addCell('Telefone', 'left', 'title');
                //$tr->addCell('Permissao Dom Ini', 'left', 'title');
                //$tr->addCell('Permissao Dom Fim', 'left', 'title');
                //$tr->addCell('Permissao Seg Ini', 'left', 'title');
                //$tr->addCell('Permissao Seg Fim', 'left', 'title');
                //$tr->addCell('Permissao Ter Ini', 'left', 'title');
                //$tr->addCell('Permissao Ter Fim', 'left', 'title');
                //$tr->addCell('Permissao Qua Ini', 'left', 'title');
                //$tr->addCell('Permissao Qua Fim', 'left', 'title');
                //$tr->addCell('Permissao Qui Ini', 'left', 'title');
                //$tr->addCell('Permissao Qui Fim', 'left', 'title');
                //$tr->addCell('Permissao Sex Ini', 'left', 'title');
                //$tr->addCell('Permissao Sex Fim', 'left', 'title');
                //$tr->addCell('Permissao Sab Ini', 'left', 'title');
                //$tr->addCell('Permissao Sab Fim', 'left', 'title');
                //$tr->addCell('Data Permitida', 'left', 'title');
                //$tr->addCell('Data Ini', 'left', 'title');
                //$tr->addCell('Data Fim', 'left', 'title');

                
                // controls the background filling
                $colour= FALSE;
                $posto = '';
                
                // data rows
                foreach ($objects as $object)
                {
                     
                    $style = $colour ? 'datap' : 'datai';
                    
                    if ($object->posto_id != $posto) {
                        
                        $desc_posto = new Posto($object->posto_id);
                        $tr->addRow();
                        $tr->addCell($desc_posto->descricao, 'left', $style, 6);
                        $colour = !$colour;                        
                        $style = $colour ? 'datap' : 'datai';
                        $posto = $object->posto_id; 
                    }

                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    $tr->addCell($object->nome, 'left', $style);
                    
                    if ($object->status == 'Y') {
                        $status = 'Liberado';
                    } else {
                        $status = 'Bloqueado';
                    }
                    
                    $tr->addCell($status, 'left', $style);
                    
                    $tr->addCell($object->motivo_funcao_finalidade, 'left', $style);
                    $tr->addCell($object->documento, 'left', $style);
                    $tr->addCell($object->telefone, 'left', $style);
                    //$tr->addCell($object->permissao_dom_ini, 'left', $style);
                    //$tr->addCell($object->permissao_dom_fim, 'left', $style);
                    //$tr->addCell($object->permissao_seg_ini, 'left', $style);
                    //$tr->addCell($object->permissao_seg_fim, 'left', $style);
                    //$tr->addCell($object->permissao_ter_ini, 'left', $style);
                    //$tr->addCell($object->permissao_ter_fim, 'left', $style);
                    //$tr->addCell($object->permissao_qua_ini, 'left', $style);
                    //$tr->addCell($object->permissao_qua_fim, 'left', $style);
                    //$tr->addCell($object->permissao_qui_ini, 'left', $style);
                    //$tr->addCell($object->permissao_qui_fim, 'left', $style);
                    //$tr->addCell($object->permissao_sex_ini, 'left', $style);
                    //$tr->addCell($object->permissao_sex_fim, 'left', $style);
                    //$tr->addCell($object->permissao_sab_ini, 'left', $style);
                    //$tr->addCell($object->permissao_sab_fim, 'left', $style);
                    //$tr->addCell($object->data_permitida, 'left', $style);
                    //$tr->addCell($object->data_ini, 'left', $style);
                    //$tr->addCell($object->data_fim, 'left', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 6);
                
                // stores the file
                if (!file_exists("app/output/Visitante.{$format}") OR is_writable("app/output/Visitante.{$format}"))
                {
                    $tr->save("app/output/Visitante.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Visitante.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Visitante.{$format}");
                
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
