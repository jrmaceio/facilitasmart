<?php
/**
 * @author  <your name here>
 */
class ContasPagarRelLiquidadas extends TPage
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasPagar_liquidadas');
        $this->form->setFormTitle('Contas a Pagar Liquidadas');
 
        $dt_inicio =new TDate('dt_inicio');
        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_fim =new TDate('dt_fim');
        $dt_fim->setMask('dd/mm/yyyy');
        
        //$classe_id = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        
        $mes_ref = new TEntry('mes_ref');
  
        $dt_lancamento = new TEntry('dt_lancamento');
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $valor = new TEntry('valor');

        $output_type = new TRadioGroup('output_type');
        
        $classe_id->setSize('100%');
        //$situacao->setSize(50);
        $mes_ref->setSize('50%');
        
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        
        $tipo_lancamento->setSize('50%');
        
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]                                
                            );
                            
        $this->form->addFields( [new TLabel('Classe')], [$classe_id],
                                [new TLabel('Mês Ref.')], [$mes_ref]                               
                            );

        $this->form->addFields( [new TLabel('Data Inicial')], [$dt_inicio],
                                [new TLabel('Data Final')], [$dt_fim]                                
                            );
        
        $change_data = new TAction(array($this, 'onChangeData'));
        $dt_inicio->setExitAction($change_data);
        $dt_fim->setExitAction($change_data);

        $this->form->addFields( [new TLabel('Output')], [$output_type]);
 
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        // add the action button

        $btn = $this->form->addAction( _t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add($this->form);
        
        // mostrar o mes ref e condominio selecionado
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
            
            parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }

        parent::add($container);
    }
    
    public static function onChangeData($param)
    {
      
        $obj = new StdClass;
        $string = new StringsUtil;
        
        if(strlen($param['dt_inicio']) == 10 && strlen($param['dt_fim']) == 10)
        {
        
            if(strtotime($string->formatDate($param['dt_fim'])) < strtotime($string->formatDate($param['dt_inicio'])))
            {
    	        $obj->data_atividade_final = ''; 
    	        new TMessage('error', 'Data de liquidacao final menor que data de liquidacao inicial'); 
            }
        
        }
        
        TForm::sendData('form_ContasPagar_avencer', $obj, FALSE, FALSE);
       
    }
    
    /**
     * Generate the report
     */
    function onGenerate($param = NULL)
    {
        try
        {
            $string = new StringsUtil;
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            //var_dump($formdata);
            
            $repository = new TRepository('ContasPagar');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_liquidacao';
                $param['direction'] = 'asc';
            }
            
            $formdata->dt_inicio = $string->formatDate($formdata->dt_inicio);
            $formdata->dt_fim = $string->formatDate($formdata->dt_fim);
            
            $criteria->setProperties($param); // order, offset
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter

            if ($formdata->conta_fechamento_id)
            {    
                $criteria->add(new TFilter('conta_fechamento_id', '=', "{$formdata->conta_fechamento_id}"));
            }
    
            if ($formdata->classe_id)
            {
                $criteria->add(new TFilter('classe_id', '=', "{$formdata->classe_id}"));
            }
            
            // titulos pagos
            $criteria->add(new TFilter('situacao', '=', "1"));
            
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$formdata->mes_ref}"));
            }
            
            if ($formdata->dt_inicio)
            {
                $criteria->add(new TFilter('dt_liquidacao', 'between', $formdata->dt_inicio, $formdata->dt_fim)); 
            }
           
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
          
                       
            if ($objects)
            {
                $widths = array(130,30,240,30,60,40);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
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
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '8', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell(TSession::getValue('resumo'), 'center', 'header', 6);
                
                $tr->addRow();
                $tr->addCell('Relação de títulos liquidados - Período de : ' . $string->formatDateBR($formdata->dt_inicio) . ' até ' . $string->formatDateBR($formdata->dt_fim), 'center', 'header', 6);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Vencimento', 'left', 'cabecalho');
                $tr->addCell('Id', 'center', 'cabecalho');
                $tr->addCell('Descrição', 'center', 'cabecalho');
                $tr->addCell('Mes Ref', 'center', 'cabecalho');
                $tr->addCell('Valor', 'center', 'cabecalho');
                $tr->addCell('Cta Fec.', 'right', 'cabecalho');

                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor = 0;
                
                $total_valor = 0;
               
                $dataliquidacao = '';
                                                       
                // data rows
                foreach ($objects as $object)
                {
                    if (  $dataliquidacao == '' ) 
                    {
                        
                        $dataliquidacao = $object->dt_liquidacao;
                        
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($dataliquidacao), 'left', 'normal', 6);
                    }
                    
                    
                    if ($dataliquidacao != $object->dt_liquidacao ){
                    
                        if ( $total_valor > 0 ) {
                            $tr->addRow();
                            $tr->addCell('', 'center', 'footer', 4);
                            $tr->addCell(number_format($total_valor, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell('', 'center', 'footer');
                           
                            // zera totalizadores
                            $total_valor = 0;
                          
                    
                        }
                    
                        $dataliquidacao = $object->dt_liquidacao;
                    
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($dataliquidacao), 'left', 'normal', 6);
                        
                    }
                
                    $classe = new PlanoContas($object->classe_id);
                        
                    $style = $colour ? 'datap' : 'datai';

                    $tr->addRow();
                    $tr->addCell(substr($classe->descricao,0,30), 'right', $style);

                    $tr->addCell($object->id, 'center', $style);
                    
                    $tr->addCell(substr($object->descricao,0,75), 'center', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    $tr->addCell($object->conta_fechamento_id, 'right', $style);
                                        
                    $total_valor += $object->valor;
                    $total_geral_valor += $object->valor;
                                       
                    $colour = !$colour;
                }
                
                // totaliza ultimo dia impresso
                $tr->addRow();
                $tr->addCell('', 'center', 'footer', 4);
                $tr->addCell(number_format($total_valor, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'center', 'footer');
                
                // footer row
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 6);
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total Geral', 'right', 'footer', 4);
                $tr->addCell(number_format($total_geral_valor, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'center', 'footer');
                
                ///////////// totalizacao por classe de conta ///////////////
                $condominio = TSession::getValue('id_condominio');
                
                $conn2 = TTransaction::get();

                $sql2 = "SELECT contas_pagar.classe_id, plano_contas.descricao, 
                        sum( contas_pagar.valor ) as valor
                        FROM contas_pagar 
                        INNER JOIN plano_contas on contas_pagar.classe_id = plano_contas.id 
                        where 
                        contas_pagar.condominio_id = " . $condominio . " and situacao = '0' and " .
                        "contas_pagar.dt_liquidacao >= '" . $formdata->dt_inicio . "' and 
                        contas_pagar.dt_liquidacao <= '" . $formdata->dt_fim . "' ";
                        ;
                
                if ($formdata->conta_fechamento_id)
                {    
                    $sql2 = $sql2 . 'and conta_fechamento_id = ' . $formdata->conta_fechamento_id;
                }
                
                if ($formdata->classe_id)
                {
                    $sql2 = $sql2 . 'and classe_id = ' . $formdata->classe_id;
                }   
            
                if ($formdata->mes_ref)
                {
                    $sql2 = $sql2 . 'and mes_ref = ' . $formdata->mes_ref;
                }
            
                $sql2 = $sql2 . " group by classe_id";
                
                $colunas2 = $conn2->query($sql2);
              
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('TOTAL ORIGINAL POR CLASSE', 'center', 'normal', 6);
                $tr->addRow();
                $tr->addCell('Classe', 'center', 'normal', 5);
                $tr->addCell('Valor', 'center', 'normal');
                
                $tot_normal = 0;
                                              
                foreach ($colunas2 as $object2) // feito pelo select
                {
                  $tr->addRow();
                  $tr->addCell($object2['descricao'], 'center', 'normal', 5);
                  $tr->addCell(number_format($object2['valor'], 2, ',', '.'), 'right', 'normal');
                  
                  $tot_normal += $object2['valor'];
                
                }

                $tr->addRow();
                $tr->addCell('TOTAL', 'center', 'normal', 5);
                $tr->addCell(number_format($tot_normal, 2, ',', '.'), 'right', 'normal');
                
                //////////////////////////////// fim totalizacao por conta classificacao //////////
                             
                $tr->addRow();
                   
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 6);
                
                // stores the file
                if (!file_exists("app/output/ContasPagarRelAVencer.{$format}") OR 
                     is_writable("app/output/ContasPagarRelAVencer.{$format}"))
                {
                    $tr->save("app/output/ContasPagarRelAVencer.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasPagarRelAVencer.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasPagarRelAVencer.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrado.');
            }
    
            $formdata->dt_inicio = $string->formatDateBR($formdata->dt_inicio);
            $formdata->dt_fim = $string->formatDateBR($formdata->dt_fim);
            
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
}
