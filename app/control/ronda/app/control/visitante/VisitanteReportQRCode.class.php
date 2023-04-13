<?php
/**
 * VisitanteReport Report
 * @author  <your name here>
 */
class VisitanteReportQRCode extends TPage
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
        $nome = new TEntry('nome');
        $status = new TCombo('status');
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao');
        $output_type = new TRadioGroup('output_type');

        $status->addItems( [ 'Y' => 'Liberado', 'N' => 'Bloqueado' ] ); 
        
        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $nome->setSize('100%');
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
            
            if ($data->nome)
            {
                $criteria->add(new TFilter('nome', 'like', "%{$data->nome}%"));
            }
            if ($data->status)
            {
                $criteria->add(new TFilter('status', 'like', "%{$data->status}%"));
            }
            if ($data->posto_id)
            {
                $criteria->add(new TFilter('posto_id', '=', "{$data->posto_id}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $properties['leftMargin']    = 12;
            $properties['topMargin']     = 12;
            $properties['labelWidth']    = 64;
            $properties['labelHeight']   = 54;
            $properties['spaceBetween']  = 4;
            $properties['rowsPerPage']   = 5;
            $properties['colsPerPage']   = 2;
            $properties['fontSize']      = 12;
            $properties['barcodeHeight'] = 20;
            $properties['imageMargin']   = 0;
            
            $label  = '' . "\n";
            $label .= '<b>Código</b>: {$id}' . "\n";
            $label .= '<b>Posto</b>: {$posto_id}' . "\n";
            $label .= '<b>Nome</b>: {$nome}' . "\n";
            $label .= '' . "\n";
            $label .= '#qrcode#' . "\n";
            //$label .= '{$id_pad}';
        
            $generator = new AdiantiBarcodeDocumentGenerator;
            $generator->setProperties($properties);
            $generator->setLabelTemplate($label);
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $posto = new Posto($object->posto_id);
                    
                    $object->posto_id = $posto->descricao;
                    $object->nome = substr($object->nome,0,18);
                    
                    //criar o qrcode do visitante com:
                    //tipo=3,id_posto=50,id_patrulheiro=650,id_unidade=5,id_visitante
                    //a unidade é a unidade configurada no adianti admin
                    
                    //o tipo=3 indica que é um visitante
                    //posto
                    //patrulheiro: neste momento o id_patrulheiro não precisa porque vai receber de quem está fazendo a ronda no momento
                    //unidade: a unidade do posto de ronda(obra)
                    //visitante: id dele, o app deve pesquisar se ele está liberado e a validade da liberacao
                    $object->codigo = '3,' .$posto->id . ',1,' . $posto->unidade_id . ',' .$object->id;
                    $generator->addObject($object);  
                   
                  
                   
                }
            }
            
            $generator->setBarcodeContent('{codigo}');
            $generator->generate();
            
            $arquivo = 'qrcodes' + rand(1, 1500) + '.pdf';
            
            $generator->save('app/output/' + $arquivo);
            
            $window = TWindow::create('QRCode de visitantes', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = 'app/output/' + $arquivo;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
            
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
