<?php
/**
*
 */
class ChequeForm extends TPage
{
    protected $form; // form
    protected $program_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cheque');
        $this->form->setFormTitle( 'Emissão de Cheque' );

        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio')));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
             
              
        // create the form fields
        $id   = new TEntry('id');
        $documento = new TEntry('documento');
        
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $mes_referencia = new TEntry('mes_referencia');
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_liquidacao = new TDate('dt_liquidacao');
        $dt_emissao = new TDate('dt_emissao');
        $valor = new TEntry('valor');
        $cheque = new TEntry('cheque');
        $nominal_a = new TEntry('nominal_a');
        
        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
       
        $criteria = new TCriteria;
        $criteria->add(new TFilter('mes_ref', '=', TSession::getValue('mesref')));
        $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
        $despesa_id = new TDBSeekButton('despesa_id', 'facilitasmart', 'form_Cheque', 'ContasPagar', 
                                        'descricao', 'despesa_id', 'program_name', $criteria);
         
        $program_name = new TEntry('program_name');
        $despesa_id->setSize('50');
        $program_name->setSize('calc(100% - 200px)');
        $program_name->setEditable(FALSE);
        
        $valor->setNumericMask(2, ',', '.');
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_liquidacao->setMask('dd/mm/yyyy');
        $dt_emissao->setMask('dd/mm/yyyy');
        
        // validations
        $dt_vencimento->addValidation('dt_vencimento', new TRequiredValidator);
        $dt_emissao->addValidation('dt_emissao', new TRequiredValidator);
        $cheque->addValidation('cheque', new TRequiredValidator);
        
        // define the sizes
        $id->setSize('30%');
        $documento->setSize('70%');
        $cheque->setSize('70%');

        // validations
        $documento->addValidation('documento', new TRequiredValidator);
        
                      
        // outras propriedades
        $id->setEditable(false);
        
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel('Condominio')], [$condominio_id]);
        $this->form->addFields( [new TLabel('Documento')], [$documento],
                                [new TLabel('Mês Referência')], [$mes_referencia]);
       
        $this->form->addFields( [new TLabel('Data Emissão')], [$dt_emissao],
                                [new TLabel('Data Vencimento')], [$dt_vencimento]);
        $this->form->addFields( [new TLabel('Data Liquidação')], [$dt_liquidacao], 
                                [new TLabel('Valor')], [$valor] );
        $this->form->addFields( [new TLabel('Cheque')], [$cheque]);
        $this->form->addFields( [new TLabel('Nominal a')], [$nominal_a]);
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]);
        
        $this->program_list = new TQuickGrid();
        $this->program_list->setHeight(200);
        $this->program_list->makeScrollable();
        $this->program_list->style='width: 100%';
        $this->program_list->id = 'program_list';
        $this->program_list->disableDefaultClick();
        $this->program_list->addQuickColumn('', 'delete', 'center', '5%');
        $this->program_list->addQuickColumn('Id', 'id', 'left', '10%');
        $this->program_list->addQuickColumn('Despesa', 'name', 'left', '85%');

        $this->program_list->createModel();
        
        $add_button  = TButton::create('add',  array($this,'onAddCtsPagar'), _t('Add'), 'fa:plus green');
        
        $hbox = new THBox;
        $hbox->add($despesa_id);
        $hbox->add($program_name, 'display:initial');
        $hbox->add($add_button);
        $hbox->style = 'margin: 4px';
        
        $vbox = new TVBox;
        $vbox->style='width:100%';
        $vbox->add( $hbox );
        $vbox->add($this->program_list);
        
        $this->form->addFields( [new TFormSeparator('Despesas')] );
        $this->form->addFields( [$vbox] );
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction( _t('Clear'), new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addAction( _t('Back'), new TAction(array('ChequeList','onReload')),  'fa:arrow-circle-o-left blue' );
        $this->form->addAction( 'Cópia Cheque Paisagem', new TAction(array($this,'onCopiaCheque')),  'fa:id-card-o green' );
        $this->form->addAction( 'Cópia Cheque Normal', new TAction(array($this,'onCopiaCheque2')),  'fa:id-card-o green' );
        
        $this->form->addField($despesa_id);
        $this->form->addField($program_name);
        $this->form->addField($add_button);
        
        $container = new TVBox;
        $container->style = 'width:90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ChequeList'));
        $container->add($this->form);
        
        // add the form to the page
        parent::add($container);
    }
    
    static function onChangeMesRef($param)
    {
         $mes_ref = $param['mes_referencia'];
         
         // grava resultado
         TSession::setValue('mes_ref_configurado', $mes_ref);
    } 
    
    /**
     * Generate the report
     */
    function onCopiaCheque2($param)
    {
        try
        {
            $string = new StringsUtil;
            
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('Cheque');
            $criteria   = new TCriteria;
            
            if ($formdata->id)
            {
                $criteria->add(new TFilter('id', '=', "{$param['id']}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = 'pdf';
            
            if ($objects)
            {
                $widths = array(150,300,55,55,75);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;    
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths); 
                        //fpdf
                        $fpdf = $tr->getNativeWriter();
                        //$fpdf->setHeaderCallback(array($this,'Cabecalho'));
                        //$this->Cabecalho($fpdf);
                        //$fpdf->setFooterCallback(array($this,'Rodape'));
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '8', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '8', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '12', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '6', 'I',  '#000000', '#A3A3A3');
                $tr->addStyle('total', 'Arial', '8', 'B',   '#110303', '#BBABAB');
                
                $condominios = Condominio::where('id', '=', $param['condominio_id'])->load();
                
                foreach ($condominios as $condominio)
                    {  
                        $tr->addRow();
                        $tr->addCell($condominio->nome, 'center', 'header', 5);     
                    }
                                
                // add a header row
                $tr->addRow();
                $tr->addCell('DADOS DO CHEQUE', 'center', 'datap', 5);
                $tr->addRow();
                $tr->addCell('Cópia do Cheque No. ' . $param['cheque'], 'center', 'datap', 2);
                $tr->addCell('Mês Referência : ' . $param['mes_referencia'], 'center', 'datap', 3);
                
                //$tr->addRow();
                //$tr->addCell(' ', 'center', 'datap', 5);
                
                $tr->addRow();
                $tr->addCell('Data Emissão : ' . $param['dt_emissao'], 'center', 'datap',2);
                $tr->addCell('Valor R$ ' . $param['valor'], 'center', 'datap', 3);
                
                $tr->addRow();
                $tr->addCell('Nominal a : ' . $param['nominal_a'], 'center', 'datap', 5);
                
                $contas = ContaFechamento::where('id', '=', $param['conta_fechamento_id'])->load();
                
                foreach ($contas as $conta)
                    {  
                        $tr->addRow();
                        $tr->addCell('Conta : ' . $conta->descricao, 'center', 'datap', 5);     
                    }
                    
                $tr->addRow();
                $tr->addCell('DETALHAMENTO DAS DESPESAS', 'center', 'title', 5);
                     
                // add titles row
                $tr->addRow();
                $tr->addCell('Classe', 'center', 'title');
                $tr->addCell('Pago a', 'left', 'title');
                $tr->addCell('Vencimento', 'center', 'title');
                $tr->addCell('Valor', 'right', 'title');
                $tr->addCell('Destino Malote', 'center', 'title');
                
                
                // pega os dados do contas a pagar (despesas) desse cheque
                $chequecontaspagars = ChequeContaspagar::where('cheque_id', '=', $param['id'])->load();
                                
                // controls the background filling
                $colour= FALSE;
                
                $total_cheque = 0;
                
                // data rows
                foreach ($chequecontaspagars as $chequecontaspagar)
                {
                    $contaspagars = ContasPagar::where('id', '=', $chequecontaspagar->contas_pagar_id)->load();
                    
                    foreach ($contaspagars as $contaspagar)
                    {    
                        $style = $colour ? 'datap' : 'datai';
                        $tr->addRow();
                        
                        $classess = PlanoContas::where('id', '=', $contaspagar->classe_id)->load();
                        foreach ($classess as $classes)
                        {
                            $tr->addCell(substr($classes->descricao,0,25), 'center', $style);
                            
                            //$fpdf->multicell(300, 12, $classes->descricao, 0, 'left',0); 
                        }                        
                        
                        $tr->addCell($contaspagar->descricao, 'left', $style);
                        $tr->addCell($string->formatDateBR($contaspagar->dt_vencimento), 'center', $style);
                        
                        $tr->addCell(number_format($contaspagar->valor, 2, ',', '.'), 'right', $style);
                        $tr->addCell('', 'left', $style);
                    
                        $colour = !$colour;
                        
                        $total_cheque += $contaspagar->valor;
                    }
                }
                
                // totalizador
                $tr->addRow();
                $tr->addCell('Total do cheque R$ ' . number_format($total_cheque, 2, ',', '.'), 'right', 'total', 5);
                
                // footer row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - ' . date('d-m-Y h:i:s'), 'center', 'footer', 5);
                // stores the file
                if (!file_exists("app/output/CopiaCheque.{$format}") OR is_writable("app/output/CopiaCheque.{$format}"))
                {
                    $tr->save("app/output/CopiaCheque.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/CopiaCheque.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/CopiaCheque.{$format}");
                
                // shows the success message
                //new TMessage('info', 'Report generated. Please, enable popups.');
                // apos criar o pdf volta para a listagem dos cheques
                TApplication::gotoPage('ChequeList', 'onReload', null); // reload
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($formdata);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Generate the report
     */
    function onCopiaCheque($param)
    {
        try
        {
            $string = new StringsUtil;
            
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('Cheque');
            $criteria   = new TCriteria;
            
            if ($formdata->id)
            {
                $criteria->add(new TFilter('id', '=', "{$param['id']}"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = 'xls';
            
            if ($objects)
            {
                $widths = array(200,400,50,50,130);
                
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
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '8', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '8', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '12', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '6', 'I',  '#000000', '#A3A3A3');
                $tr->addStyle('total', 'Arial', '8', 'B',   '#110303', '#BBABAB');
                
                $condominios = Condominio::where('id', '=', $param['condominio_id'])->load();
                
                foreach ($condominios as $condominio)
                    {  
                        $tr->addRow();
                        $tr->addCell($condominio->nome, 'center', 'header', 5);     
                    }
                                
                // add a header row
                $tr->addRow();
                $tr->addCell('DADOS DO CHEQUE', 'center', 'datap', 5);
                $tr->addRow();
                $tr->addCell('Cópia do Cheque No. ' . $param['cheque'], 'center', 'datap', 2);
                $tr->addCell('Mês Referência : ' . $param['mes_referencia'], 'center', 'datap', 3);
                
                //$tr->addRow();
                //$tr->addCell(' ', 'center', 'datap', 5);
                
                $tr->addRow();
                $tr->addCell('Data Emissão : ' . $param['dt_emissao'], 'center', 'datap',2);
                $tr->addCell('Valor R$ ' . $param['valor'], 'center', 'datap', 3);
                
                $tr->addRow();
                $tr->addCell('Nominal a : ' . $param['nominal_a'], 'center', 'datap', 5);
                
                $contas = ContaFechamento::where('id', '=', $param['conta_fechamento_id'])->load();
                
                foreach ($contas as $conta)
                    {  
                        $tr->addRow();
                        $tr->addCell('Conta : ' . $conta->descricao, 'center', 'datap', 5);     
                    }
                    
                $tr->addRow();
                $tr->addCell('DETALHAMENTO DAS DESPESAS', 'center', 'title', 5);
                     
                // add titles row
                $tr->addRow();
                $tr->addCell('Classe', 'center', 'title');
                $tr->addCell('Pago a', 'left', 'title');
                $tr->addCell('Vencimento', 'center', 'title');
                
                $tr->addCell('Valor', 'right', 'title');
                $tr->addCell('Destino Malote', 'center', 'title');
                
                // pega os dados do contas a pagar (despesas) desse cheque
                $chequecontaspagars = ChequeContaspagar::where('cheque_id', '=', $param['id'])->load();
                                
                // controls the background filling
                $colour= FALSE;
                
                $total_cheque = 0;
                
                // data rows
                foreach ($chequecontaspagars as $chequecontaspagar)
                {
                    $contaspagars = ContasPagar::where('id', '=', $chequecontaspagar->contas_pagar_id)->load();
                    
                    foreach ($contaspagars as $contaspagar)
                    {    
                        $style = $colour ? 'datap' : 'datai';
                        $tr->addRow();
                        
                        $classess = PlanoContas::where('id', '=', $contaspagar->classe_id)->load();
                        foreach ($classess as $classes)
                        {
                            $tr->addCell($classes->descricao, 'center', $style);
                        }                        
                        
                        $tr->addCell($contaspagar->descricao, 'left', $style);
                        $tr->addCell($string->formatDateBR($contaspagar->dt_vencimento), 'center', $style);
                        
                        $tr->addCell(number_format($contaspagar->valor, 2, ',', '.'), 'right', $style);
                        $tr->addCell('', 'left', $style);
                    
                        $colour = !$colour;
                        
                        $total_cheque += $contaspagar->valor;
                    }
                }
                
                // totalizador
                $tr->addRow();
                $tr->addCell('Total do cheque R$ ' . number_format($total_cheque, 2, ',', '.'), 'right', 'total', 5);
                
                // footer row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - ' . date('d-m-Y h:i:s'), 'center', 'footer', 5);
                // stores the file
                if (!file_exists("app/output/CopiaCheque.{$format}") OR is_writable("app/output/CopiaCheque.{$format}"))
                {
                    $tr->save("app/output/CopiaCheque.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/CopiaCheque.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/CopiaCheque.{$format}");
                
                // shows the success message
                //new TMessage('info', 'Report generated. Please, enable popups.');
                // apos criar o pdf volta para a listagem dos cheques
                TApplication::gotoPage('ChequeList', 'onReload', null); // reload
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($formdata);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Remove program from session
     */
    public static function deleteProgram($param)
    {
        $programs = TSession::getValue('program_list');
        unset($programs[ $param['id'] ]);
        TSession::setValue('program_list', $programs);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        $string = new StringsUtil;
        
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('facilitasmart');
                     
            // get the form data into an active record System_group
            $object = new Cheque;
            $object->fromArray( $param );
            
                        
            $object->dt_vencimento = TDate::date2us($object->dt_vencimento );
            $object->dt_emissao = TDate::date2us($object->dt_emissao );
            $object->dt_liquidacao = TDate::date2us($object->dt_liquidacao );
            $object->valor ? $object->valor = $string->desconverteReais($object->valor) : null;
            
            // conferencia da soma das despesas
            $total_despesas = 0;
            $despesas = TSession::getValue('program_list');
            if (!empty($despesas))
            {
                foreach ($despesas as $despesa)
                {
                    $desp = new ContasPagar( $despesa['id'] );
                    $total_despesas += $desp->valor;
                }
            }
            //////////////////////
            
            $valor_cheque = (float) $object->valor;
 
            if ( number_format($total_despesas, 2, ',', '.') != number_format((float) $object->valor, 2, ',', '.') )  {
                TTransaction::close(); // close the transaction
                new TMessage('info', 'Confira o valor do cheques ['. number_format((float) $object->valor, 2, ',', '.') . 
                                        '] e a soma das despesas selecionadas [' . number_format($total_despesas, 2, ',', '.') . '] !');    
                return;
            }

            $object->store();
            $object->clearParts();
            
            
            $programs = TSession::getValue('program_list');
            if (!empty($programs))
            {
                foreach ($programs as $program)
                {
                    $object->addContasPagar( new ContasPagar( $program['id'] ) );

                }
            }
            
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_Cheque', $data);
            
            TTransaction::close(); // close the transaction
            new TMessage('info', _t('Record saved')); // shows the success message
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
      try
        {
          if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('facilitasmart');
                
                // instantiates object System_group
                $object = new Cheque($key);
                
                $data = array();
                
                foreach ($object->getContasPagar() as $program)
                {
                    $data[$program->id] = $program->toArray();
                    
                    $item = new stdClass;
                    $item->id = $program->id;
                    $item->name = $program->descricao . ' [R$ ' . $program->valor . ']';
                    
                    $i = new TElement('i');
                    $i->{'class'} = 'fa fa-trash red';
                    $btn = new TElement('a');
                    $btn->{'onclick'} = "__adianti_ajax_exec('class=ChequeForm&method=deleteProgram&id={$program->id}');$(this).closest('tr').remove();";
                    $btn->{'class'} = 'btn btn-default btn-sm';
                    $btn->add( $i );
                    
                    $item->delete = $btn;
                    $tr = $this->program_list->addItem($item);
                    $tr->{'style'} = 'width: 100%;display: inline-table;';
                }
                
                // necessário no mysql
                $object->dt_emissao = TDate::date2br($object->dt_emissao); 
                $object->dt_vencimento = TDate::date2br($object->dt_vencimento);
                $object->dt_liquidacao = TDate::date2br($object->dt_liquidacao);
                $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
                
                TSession::setValue('program_list', $data);
            }
            else
            {
                $this->form->clear();
                TSession::setValue('program_list', null);
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Add 
     */
    public static function onAddCtsPagar($param)
    {
       
        try
        {
            $id = $param['despesa_id'];
           
            $program_list = TSession::getValue('program_list');
            
            //var_dump(!empty($id) AND empty($program_list[$id]));
            
            if (!empty($id) AND empty($program_list[$id]))
            {
                TTransaction::open('facilitasmart');
                $program = ContasPagar::find($id);
                $program_list[$id] = $program->toArray();
                TSession::setValue('program_list', $program_list);
                TTransaction::close();
            
                //var_dump($program_list[$id]);
                    
                $i = new TElement('i');
                $i->{'class'} = 'fa fa-trash red';
                $btn = new TElement('a');
                $btn->{'onclick'} = "__adianti_ajax_exec(\'class=ChequeForm&method=deleteProgram&id=$id\');$(this).closest(\'tr\').remove();";
                $btn->{'class'} = 'btn btn-default btn-sm';
                $btn->add($i);
                               
                $tr = new TTableRow;
                $tr->{'class'} = 'tdatagrid_row_odd';
                $tr->{'style'} = 'width: 100%;display: inline-table;';
                $cell = $tr->addCell( $btn );
                $cell->{'style'}='text-align:center';
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '5%';
                $cell = $tr->addCell( $program->id );
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '10%';
                $cell = $tr->addCell( $program->descricao . '[ R$ ' . $program->valor . ' ]');
                $cell->{'class'}='tdatagrid_cell';
                $cell->{'width'} = '85%';
                
                TScript::create("tdatagrid_add_serialized_row('program_list', '$tr');");
                
                $data = new stdClass;
                $data->despesa_id = '';
                $data->program_name = '';
                TForm::sendData('form_Cheque', $data);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
