<?php
/**
 * @author  <your name here>
 */
class ContasReceberRelAVencer extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ContasReceber_avencer');
        $this->form->setFormTitle('Contas a Receber a Vencer');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
        
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
        $cobranca->setSize('50%');
        $unidade_id->setSize('100%');
        
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        
        $tipo_lancamento->setSize('50%');
        
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]                                
                            );
                            
        $this->form->addFields( [new TLabel('Classe')], [$classe_id],
                                [new TLabel('Mês Ref.')], [$mes_ref]                               
                            );

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id],
                                [new TLabel('Cobrança')], [$cobranca]                                
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
        
        TForm::sendData('form_ContasReceber_avencer', $obj, FALSE, FALSE);
       
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
            
            $repository = new TRepository('ContasReceber');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento, unidade_id, mes_ref';
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
            
            if ($formdata->cobranca)
            {
                $criteria->add(new TFilter('cobranca', '=', "{$formdata->cobranca}"));
            }
    
            if ($formdata->classe_id)
            {
                $criteria->add(new TFilter('classe_id', '=', "{$formdata->classe_id}"));
            }
            
            // titulos pagos
            $criteria->add(new TFilter('situacao', '=', "0"));
            
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', '=', "{$formdata->mes_ref}"));
            }
            
            if ($formdata->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$formdata->unidade_id}"));
            }
            
            if ($formdata->dt_inicio)
            {
                $criteria->add(new TFilter('dt_vencimento', 'between', $formdata->dt_inicio, $formdata->dt_fim)); 
            }
           
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
          
                       
            if ($objects)
            {
                $widths = array(130,30,70,170,30,60,600);
                
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
                $tr->addCell(TSession::getValue('resumo'), 'center', 'header', 7);
                
                $tr->addRow();
                $tr->addCell('Relação de títulos a vencer - Período de : ' . $string->formatDateBR($formdata->dt_inicio) . ' até ' . $string->formatDateBR($formdata->dt_fim), 'center', 'header', 7);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Vencimento', 'left', 'cabecalho');
                $tr->addCell('Id', 'center', 'cabecalho');
                $tr->addCell('Unidade', 'center', 'cabecalho');
                $tr->addCell('Responsável', 'center', 'cabecalho');
                $tr->addCell('Mes Ref', 'center', 'cabecalho');
                $tr->addCell('Valor', 'center', 'cabecalho');
                $tr->addCell('Link', 'right', 'cabecalho');

                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor_lancado = 0;
                $total_geral_valor = 0;
                $total_geral_multa = 0;
                $total_geral_juros = 0;
                $total_geral_desc = 0;
                $total_geral_tarifa = 0;
                $total_geral_creditado = 0;
                
                $total_valor_lancado = 0;
                $total_valor = 0;
                $total_multa = 0;
                $total_juros = 0;
                $total_desc = 0;
                $total_tarifa = 0;
                $total_creditado = 0;
               
                $unidade = '';
                $datavencimento = '';
                                                       
                // data rows
                foreach ($objects as $object)
                {
                    if (  $datavencimento == '' ) 
                    {
                        
                        $datavencimento = $object->dt_vencimento;
                        
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($datavencimento), 'left', 'normal', 7);
                    }
                    
                    
                    if ($datavencimento != $object->dt_vencimento ){
                    
                        if ( $total_valor > 0 ) {
                            $tr->addRow();
                            $tr->addCell('', 'center', 'footer', 5);
                            $tr->addCell(number_format($total_valor, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell('', 'center', 'footer');
                           
                            // zera totalizadores
                            $total_valor = 0;
                          
                    
                        }
                    
                        $datavencimento = $object->dt_vencimento;
                    
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '7', '',    '#000000', '#EEEEEE');
                        $tr->addRow();
                        $tr->addCell($string->formatDateBR($datavencimento), 'left', 'normal', 7);
                        
                    }
                
                    $unidade = new Unidade($object->unidade_id);
                    
                    $proprietario = new Pessoa($unidade->proprietario_id);
                                   
                    $classe = new PlanoContas($object->classe_id);
                        
                    $style = $colour ? 'datap' : 'datai';

                    $tr->addRow();
                    $tr->addCell($classe->descricao, 'right', $style);

                    $tr->addCell($object->id, 'center', $style);
                    
                    //$tr->addCell($unidade->descricao, 'center', $style);
                    if (isset($unidade->descricao)) {
                        $tr->addCell($unidade->bloco_quadra . '-' . $unidade->descricao, 'center', $style);
                        
                    } else {
                        $tr->addCell(substr($object->descricao,0,70), 'center', $style);
                        
                    }
                    
                    $tr->addCell($object->nome_responsavel, 'center', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    $tr->addCell($object->pjbank_linkBoleto, 'right', $style);
                                        
                    $total_valor += $object->valor;
                    $total_geral_valor += $object->valor;
                                       
                    $colour = !$colour;
                }
                
                // totaliza ultimo dia impresso
                $tr->addRow();
                $tr->addCell('', 'center', 'footer', 5);
                $tr->addCell(number_format($total_valor, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'center', 'footer');
                
                // footer row
                $tr->addRow();
                $tr->addCell('          ', 'center', 'datai', 7);
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total Geral', 'right', 'footer', 5);
                $tr->addCell(number_format($total_geral_valor, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(' ', 'center', 'footer');
                
                ///////////// totalizacao por classe de conta ///////////////
                $condominio = TSession::getValue('id_condominio');
                
                $conn2 = TTransaction::get();

                $sql2 = "SELECT contas_receber.classe_id, plano_contas.descricao, 
                        sum( contas_receber.valor ) as valor
                        FROM contas_receber 
                        INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
                        where 
                        contas_receber.condominio_id = " . $condominio . " and situacao = '0' and " .
                        "contas_receber.dt_vencimento >= '" . $formdata->dt_inicio . "' and 
                        contas_receber.dt_vencimento <= '" . $formdata->dt_fim . "' ";
                        ;
                
                if ($formdata->conta_fechamento_id)
                {    
                    $sql2 = $sql2 . 'and conta_fechamento_id = ' . $formdata->conta_fechamento_id;
                }
            
                if ($formdata->cobranca)
                {
                    $sql2 = $sql2 . 'and cobranca = ' . $formdata->cobranca;
                }
    
                if ($formdata->classe_id)
                {
                    $sql2 = $sql2 . 'and classe_id = ' . $formdata->classe_id;
                }   
            
                if ($formdata->mes_ref)
                {
                    $sql2 = $sql2 . 'and mes_ref = ' . $formdata->mes_ref;
                }
            
                if ($formdata->unidade_id)
                {
                    $sql2 = $sql2 . ' and unidade_id = ' . $formdata->unidade_id;
                }
            
                $sql2 = $sql2 . " group by classe_id";
                
                $colunas2 = $conn2->query($sql2);
              
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('TOTAL ORIGINAL POR CLASSE', 'center', 'normal', 7);
                $tr->addRow();
                $tr->addCell('Classe', 'center', 'normal', 6);
                $tr->addCell('Valor', 'center', 'normal');
                
                $tot_normal = 0;
                                              
                foreach ($colunas2 as $object2) // feito pelo select
                {
                  $tr->addRow();
                  $tr->addCell($object2['descricao'], 'center', 'normal', 6);
                  $tr->addCell(number_format($object2['valor'], 2, ',', '.'), 'right', 'normal');
                  
                  $tot_normal += $object2['valor'];
                
                }

                $tr->addRow();
                $tr->addCell('TOTAL', 'center', 'normal', 6);
                $tr->addCell(number_format($tot_normal, 2, ',', '.'), 'right', 'normal');
                
                //////////////////////////////// fim totalizacao por conta classificacao //////////
                             
                $tr->addRow();
                   
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 7);
                
                // stores the file
                if (!file_exists("app/output/ContasReceberRelAVencer.{$format}") OR 
                     is_writable("app/output/ContasReceberRelAVencer.{$format}"))
                {
                    $tr->save("app/output/ContasReceberRelAVencer.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberRelAVencer.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberRelAVencer.{$format}");
                
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
