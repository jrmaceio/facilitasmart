<?php
/**
 * ListagerarboletosReport Report
 * @author  <your name here>
 */
class ListagerarboletosReportLote extends TPage
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
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
                        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Listagerarboletos_report');
        $this->form->setFormTitle('Listagem para geração de boletos');
        

        // create the form fields
        
        $bloco_quadra = new TEntry('bloco_quadra');
        $descricao = new TEntry('descricao');
        //$nome = new TEntry('nome');
        $output_type = new TRadioGroup('output_type');
        $gera_titulo = new THidden('gera_titulo');

        // add the fields
        $this->form->addFields( [ new TLabel('Bloco Quadra') ], [ $bloco_quadra ] );
        $this->form->addFields( [ new TLabel('Unidade') ], [ $descricao ] );
        //$this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $bloco_quadra->setSize('100%');
        $descricao->setSize('100%');
        //$nome->setSize('100%');
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
            
            $repository = new TRepository('ListagerarboletosLote');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'bloco_quadra,descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            
            // somente um condominio selecionado em mes referencia 
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            $criteria->add(new TFilter('gera_titulo', '=', 'Y')); // add the session filter
            
           
            if ($data->bloco_quadra)
            {
                $criteria->add(new TFilter('bloco_quadra', 'like', "%{$data->bloco_quadra}%"));
            }
            if ($data->descricao)
            {
                $criteria->add(new TFilter('descricao', 'like', "%{$data->descricao}%"));
            }
                       
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(30,50,300,50,50,50,50,200);
                
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
                $tr->addStyle('datap', 'Arial', '9', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '9', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                $condominio = new Condominio( TSession::getValue('id_condominio'));
                
                // add a header row
                $tr->addRow();
                $tr->addCell($condominio->resumo,'center', 'header', 8);
                $tr->addRow();
                $tr->addCell('Listagem para Geração de Boletos', 'center', 'title', 8);
                
                // add titles row
                $tr->addRow();
                //$tr->addCell('Condominio Id', 'right', 'title');
                //$tr->addCell('Resumo', 'left', 'title');
                 $tr->addCell('Bl Qd', 'center', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Nome', 'left', 'title');
                $tr->addCell('Boleto', 'center', 'title');
                $tr->addCell('Grupo', 'center', 'title');
                $tr->addCell('Valor', 'center', 'title');
                $tr->addCell('Desconto', 'center', 'title');
                $tr->addCell('Observação', 'center', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                $total_unidades = 0;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    //$tr->addCell($object->condominio_id, 'right', $style);
                    //$tr->addCell($object->resumo, 'left', $style);
                    $tr->addCell($object->bloco_quadra, 'center', $style);
                    $tr->addCell($object->descricao, 'center', $style);
                    $tr->addCell($object->nome, 'left', $style);
                    
                    if ($object->gera_titulo == 'Y') {
                        $tr->addCell('Sim', 'center', $style);
                    } else {
                        $tr->addCell('Não', 'center', $style);
                    }
                    
                    $tr->addCell($object->grupo_id, 'center', $style);
                    $tr->addCell($object->valor_titulo, 'right', $style);
                    $tr->addCell($object->desconto_titulo, 'right', $style);
                    $tr->addCell('           ', 'right', $style);

                    $total_unidades++;
                    
                    $colour = !$colour;
                }
                
                $tr->addRow();
                $tr->addCell('Total de Unidades : '.$total_unidades, 'center', 'footer', 8);
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 8);
                
                // stores the file
                if (!file_exists("app/output/ListagerarboletosLote.{$format}") OR is_writable("app/output/ListagerarboletosLote.{$format}"))
                {
                    $tr->save("app/output/ListagerarboletosLote.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ListagerarboletosLote.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ListagerarboletosLote.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrado.');
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
