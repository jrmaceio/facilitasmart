<?php
/**
 * RondaReport Report
 * @author  <your name here>
 */
class RondaReport extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Ronda_report');
        $this->form->setFormTitle('Ronda Report');
        

        // create the form fields
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $unidade_id = new TDBCombo('unidade_id','permission','SystemUnit','id','name', 'name', $criteria);
        //$tipo_id = new TEntry('tipo_id');
        
        $dt_inicio =new TDate('dt_inicio');
        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_fim =new TDate('dt_fim');
        $dt_fim->setMask('dd/mm/yyyy');
        
        $hora_ronda = new TEntry('hora_ronda');

        $patrulheiro_id = new TDBUniqueSearch('patrulheiro_id', 'ronda', 'Patrulheiro', 'id', 'nome');
        $ponto_ronda_id = new TDBUniqueSearch('ponto_ronda_id', 'ronda', 'PontoRonda', 'id', 'descricao');
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao');
        $ordem = new TRadioGroup('ordem');
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Unidade') ], [ $unidade_id ] );
        //$this->form->addFields( [ new TLabel('Tipo') ], [ $tipo_id ] );
        $this->form->addFields( [ new TLabel('Hora') ], [ $hora_ronda ] );
        
        $this->form->addFields( [new TLabel('Dt. Inicial')], [$dt_inicio],
                                [new TLabel('Dt. Final')], [$dt_fim]                                
                            );

        $this->form->addFields( [ new TLabel('Patrulheiro') ], [ $patrulheiro_id ] );
        $this->form->addFields( [ new TLabel('Ponto Ronda') ], [ $ponto_ronda_id ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Ordem') ], [ $ordem ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $unidade_id->setSize('100%');
        //$tipo_id->setSize('100%');
        $hora_ronda->setSize('100%');
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        $patrulheiro_id->setSize('100%');
        $ponto_ronda_id->setSize('100%');
        $posto_id->setSize('100%');
        $ordem->setSize('100%');
        $output_type->setSize('100%');

        $ordem->addItems(array('1'=>'Posto-Dt', '2'=>'Dt-Hora'));
        $ordem->setLayout('horizontal');
        $ordem->setUseButton();
        $ordem->setValue('1');
        $ordem->setSize(70);
        
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
            
            $repository = new TRepository('Ronda');
            $criteria   = new TCriteria;
            
            if ($data->ordem == 1 ) {
                $param['order'] = 'ponto_ronda_id, data_ronda, hora_ronda'; 
            } else {
                $param['order'] = 'data_ronda, hora_ronda'; 
            }
            
            $param['direction'] = 'asc';
            $criteria->setProperties($param);
            
            $data->data_inicio = TDate::date2us($data->dt_inicio);
            $data->data_fim = TDate::date2us($data->dt_fim);
            
            if ($data->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$data->unidade_id}"));
            } else {
                $criteria->add(new TFilter('unidade_id', 'IN', TSession::getValue('userunitids')));
            }
            //if ($data->tipo_id)
            //{
            //    $criteria->add(new TFilter('tipo_id', 'like', "%{$data->tipo_id}%"));
            //}
            if ($data->hora_ronda)
            {
                $criteria->add(new TFilter('hora_ronda', 'like', "%{$data->hora_ronda}%"));
            }
            if ($data->dt_inicio)
            {
                $criteria->add(new TFilter('data_ronda', 'between', $data->data_inicio, $data->data_fim));
            }
            if ($data->patrulheiro_id)
            {
                $criteria->add(new TFilter('patrulheiro_id', '=', "{$data->patrulheiro_id}"));
            }
            if ($data->ponto_ronda_id)
            {
                $criteria->add(new TFilter('ponto_ronda_id', '=', "{$data->ponto_ronda_id}"));
            }
            if ($data->posto_id)
            {
                $criteria->add(new TFilter('posto_id', '=', "{$data->posto_id}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(50,50,50,80,80,80,200,200,160,200);
                
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
                $tr->addCell('Relatório de Rondas', 'center', 'header', 10);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'title');
                $tr->addCell('Un', 'right', 'title');
                $tr->addCell('Tipo', 'right', 'title');
                $tr->addCell('Data', 'right', 'title');
                $tr->addCell('Hora', 'right', 'title');
                $tr->addCell('Status', 'right', 'title');
                $tr->addCell('Patrulheiro', 'right', 'title');
                $tr->addCell('Ponto Ronda', 'right', 'title');
                $tr->addCell('Posto', 'right', 'title');
                $tr->addCell('Lat Long', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    $tr->addCell($object->unidade_id, 'right', $style);
                    $tr->addCell($object->tipo_id, 'right', $style);
                    $tr->addCell(TDate::date2br($object->data_ronda), 'right', $style);
                    $tr->addCell($object->hora_ronda, 'right', $style);
                    $tr->addCell($object->status_tratamento, 'right', $style);
                    
                    $patrulheiro = new Patrulheiro($object->patrulheiro_id);
                    $tr->addCell($patrulheiro->nome, 'right', $style);
                    
                    $ponto = new PontoRonda($object->ponto_ronda_id);
                    $tr->addCell($ponto->descricao, 'right', $style);
                    
                    $posto = new Posto($object->posto_id);
                    $tr->addCell($posto->descricao, 'right', $style);
                    
                    $tr->addCell($object->latitude .','. $object->longitude, 'right', $style);
                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 10);
                
                // stores the file
                if (!file_exists("app/output/Ronda.{$format}") OR is_writable("app/output/Ronda.{$format}"))
                {
                    $tr->save("app/output/Ronda.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Ronda.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Ronda.{$format}");
                
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
