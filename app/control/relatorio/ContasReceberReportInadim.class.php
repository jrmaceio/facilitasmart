<?php
/**
 * ContasReceberReportInadim Report
 * @author  <your name here>      
 *
 * $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
 */
class ContasReceberReportInadim extends TPage
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
        
        $string = new StringsUtil;

        // creates the form
        $this->form = new BootstrapFormBuilder('form_ContasReceber_report');
        $this->form->setFormTitle('Inadimplência Detalhada por Unidade');

        // define the form title
        //$this->form->setFormTitle('Inadimplência Detalhada por Unidade');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');

        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
            
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        //$situacao = new TEntry('situacao');
        $mes_ref = new TEntry('mes_ref');
       
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $honorario = new TEntry('honorario');
        
        $inad_ate = new TDate('inad_ate');
        $inad_ate->setMask('dd/mm/yyyy');
        
        $data_base = new TDate('data_base');
        $data_base->setMask('dd/mm/yyyy');
        
        $output_type = new TRadioGroup('output_type');
        $output_type->addValidation('Output', new TRequiredValidator);
        
        $unidade_nome = new TEntry('unidade_nome');
        
        $classe_id->setSize('100%');
        //$situacao->setSize(50);
        $mes_ref->setSize('100%');
        $cobranca->setSize('50%');
        $unidade_id->setSize('50%');
        $unidade_nome->setSize('100%');
        
        $multa->setSize('80%');
        $juros->setSize('80%');
        $honorario->setSize('80%');
        
        $inad_ate->setSize('100%');
        $data_base->setSize('100%');
        $output_type->setSize('100%');
        
        $unidade_nome->setEditable(FALSE);
        
        $multa->setNumericMask(2, ',', '.');
        $juros->setNumericMask(3, ',', '.');
        $honorario->setNumericMask(2, ',', '.');
        
        // mostrar o mes ref e imovel selecionado
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            
            $multa->setValue($condominio->multa);
            $juros->setValue(number_format($condominio->juros/30,3));
            $honorario->setValue(0.00);
            
            $inad_ate->setValue(date("d/m/Y"));
            $data_base->setValue(date("d/m/Y"));
            
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        // add the fields
        //$this->form->addQuickFields('% Multa', array($multa,
        //new TLabel('% Juros ao dia'),$juros, 
        //new TLabel('Correção'), $correcao
        //));
        
        $this->form->addFields( [new TLabel('% Multa')], [$multa],
                                [new TLabel('% Juros ao dia')], [$juros],
                                [new TLabel('Honorário %')], [$honorario]                                
                            );

        //$this->form->addQuickFields('Inadimplência até', array($inad_ate,
        //new TLabel('Data Base'), $data_base
        //));

        $this->form->addFields( [new TLabel('Inadimplência até')], [$inad_ate],
                                [new TLabel('Data Base')], [$data_base]                              
                            ); 
         
        //$this->form->addQuickFields('Classe Id', array($classe_id, 
        //new TLabel('Mês Referência'),$mes_ref, new TLabel('Cobrança'),$cobranca,
        //));
        $this->form->addContent([new TFormSeparator('Filtros', '#333333', '18', '#eeeeee')]); 

        $this->form->addFields( [new TLabel('Classe Id')], [$classe_id],
                                [new TLabel('Mês Referência')], [$mes_ref],
                                [new TLabel('Cobrança')], [$cobranca]                              
                            ); 

        $this->form->addFields( [new TLabel('Unidade')], [$unidade_id]);
        
        // set exit action for input_exit
        $this->form->addFields( [new TLabel('Output')], [$output_type]);

        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF', 'xls' => 'XLS'));
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
        // add the action button
        $btn = $this->form->addAction( _t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add($this->form);
        
               
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
                        
        parent::add($container);
    }
    
    public static function onExitIdUnidade($param)
    {
        try
        {
            if ($param['unidade_id']=='') {
              $obj = new StdClass;
              $obj->unidade_nome = '';
              TForm::sendData('form_ContasReceber_report', $obj);
        
              return;
            }
            
            TTransaction::open('facilitasmart');
            $unidade = new Unidade($param['unidade_id']);
            $proprietario = new Pessoa( $unidade->proprietario_id );
            $unidade_prop_nome = $proprietario->nome;
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        $obj = new StdClass;
        //$obj->unidade_descricao = $unidade_desc;
        $obj->unidade_nome = $unidade->descricao . ' - ' . $unidade_prop_nome;
        TForm::sendData('form_ContasReceber_report', $obj);
        
        
        //new TMessage('info', 'Message on field exit. <br>You have typed: ' . $param['input_exit']);
    }
    
    
    function Cabecalho()
    {
        //$this->SetY(5);
        //$this->Cell(0, 10, utf8_decode('NOME DA SUA EMPRESA'),0,0,'C');
    }
        
    /**
     * Generate the report
     */
    function onGenerate($param = NULL)
    {
        try
        {
            
            $string = new StringsUtil;
                      
            // open a transaction with database 'facilitasmartsmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('ContasReceber');

            // inclui novos filtros
            $filtro = ' ';
            
            //var_dump($formdata->mes_ref);
            if ($formdata->mes_ref)
            {
                $filtro = $filtro . ' and mes_ref = "' . $formdata->mes_ref.'"';
                
            }
            
                      
            if ($formdata->unidade_id)
            {
                $filtro = $filtro . ' and unidade_id = ' . $formdata->unidade_id;
            }
            
            if ($formdata->cobranca)
            {
                $filtro = $filtro . ' and cobranca = "' . $formdata->cobranca.'"';
                
            }
            
           
            if ($formdata->classe_id)
            {
                $filtro = $filtro .' and classe_id = ' . $formdata->classe_id;
            }
            
            if (!$formdata->inad_ate)
            {
                new TMessage('info', 'Preencha o campo Inadimplência até : xx/xx/xxxx');
                return;
            }
            
            if (!$formdata->data_base)
            {
                new TMessage('info', 'Preencha o campo Data Base : xx/xx/xxxx');
                return;
            }
           
                   
            //var_dump($formdata->inad_ate);
            $inadimplencia_ate = $string->formatDate($formdata->inad_ate);
            $data_base = $string->formatDate($formdata->data_base);
            
            /*
            SELECT contas_receber.id, contas_receber.unidade_id, contas_receber.cobranca, 
                    contas_receber.classe_id, contas_receber.dt_vencimento, contas_receber.valor, 
                    contas_receber.situacao,
                    contas_receber.mes_ref, unidade.descricao FROM contas_receber 
                    INNER JOIN unidade on contas_receber.unidade_id = unidade.id 
                    where 
                        contas_receber.condominio_id =13 and 
                        (
                        (contas_receber.situacao = 0 and contas_receber.dt_vencimento <= '2019-03-22' ) or 
                        (contas_receber.situacao = 1 and contas_receber.dt_pagamento > '2019-03-22' and 
                          contas_receber.dt_vencimento <= '2019-03-22') or
                        ((select data_base_acordo from acordo where id = contas_receber.numero_acordo) > '2019-03-22' and situacao = 2 and contas_receber.dt_vencimento <= '2019-03-22')
                        ) and unidade_id=2613
            
            */
            
            // select 
            // selecionar a partir de out/2019 ====> "contas_receber.dt_vencimento > '2017-10-01' and " .
            $condominio_id = TSession::getValue('id_condominio');
            $conn = TTransaction::get();
            $sql = "SELECT contas_receber.id, contas_receber.unidade_id, contas_receber.cobranca, 
                    contas_receber.classe_id, contas_receber.dt_vencimento, contas_receber.valor, 
                    contas_receber.situacao,
                    contas_receber.mes_ref, unidade.descricao FROM contas_receber 
                    INNER JOIN unidade on contas_receber.unidade_id = unidade.id 
                    where 
                        contas_receber.condominio_id = " . $condominio_id . " and " .                        
                        "(
                        (contas_receber.situacao = 0 and contas_receber.dt_vencimento <= '".$inadimplencia_ate."') or 
                        (contas_receber.situacao = 1 and contas_receber.dt_pagamento > '" . $inadimplencia_ate."' and 
                          contas_receber.dt_vencimento <= '" . $inadimplencia_ate."') or
                        ((select data_base_acordo from acordo where id = contas_receber.numero_acordo) > '" . 
                        $inadimplencia_ate."' and situacao = 2 and contas_receber.dt_vencimento <= '".$inadimplencia_ate."')
                        )";
                        
            
            if ($filtro) {
                $sql = $sql . " " . $filtro;
            }
                        
            $sql = $sql . " order by bloco_quadra, descricao, dt_vencimento";
                                     
            $colunas = $conn->query($sql);

            $format  = $formdata->output_type;
            
     
            if ( $formdata->multa ) {
                $perc_multa = str_replace(",",".", $formdata->multa);
            }else {
                $perc_multa = 0.00;
            }
            
            if ( $formdata->juros ) {
                $perc_juros = str_replace(",",".",$formdata->juros);
            }else {
                $perc_juros = 0.000;
            }

            if ( $formdata->honorario ) {
                $perc_honorario = str_replace(",",".",$formdata->honorario);
            }else {
                $perc_honorario = 0.00;
            }

            //var_dump($perc_honorario);

            if ($colunas)
            {
                $widths = array(35,40,20,120,55,60,40,40,40,60);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#000000', '#ffffff');
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#000000', '#ffffff');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#000000', '#ffffff');
                $tr->addStyle('footer', 'Times', '8', 'B',  '#000000', '#ffffff');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - Gestão Condominial', 'center', 'cabecalho', 10);
                ////////////teste erro do xls excel $tr->getNativeWriter();
                $tr->addRow();
                
                $tr->addCell(utf8_decode(TSession::getValue('resumo')), 'center', 'header', 10);
                
                $tr->addRow();
                $tr->addCell(utf8_decode('Inadimplência'), 'center', 'header', 10);

                $tr->addRow();
                $tr->addCell('Inadimplência até ' . $string->formatDateBR($inadimplencia_ate) . ' para contas emitidas, baixadas e sub judice2', 'left', 'header', 10);
                $tr->addRow();
                $tr->addCell('Correção: não aplicada Multa: '.$perc_multa.'% do montante Juros: '.$perc_juros.'% ao dia Data Base: '. $string->formatDateBR($data_base), 'left', 'header', 10);
                $tr->addRow();
                $tr->addCell('* Título(s) pago(s) ou em acordo após a data da inadimplência.', 'left', 'header', 10);
                $tr->addRow();
                $tr->addCell('Obs.: podem existir contas baixadas ou acordadas que se encontravam em aberto em ' . $string->formatDateBR($inadimplencia_ate), 'left', 'header', 10);
    
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'right', 'cabecalho');
                $tr->addCell('Mes Ref', 'center', 'cabecalho');
                $tr->addCell('Cob', 'center', 'cabecalho');
                $tr->addCell('Classe', 'left', 'cabecalho');
                $tr->addCell('Dt Vencimento', 'center', 'cabecalho');
                $tr->addCell('Valor', 'right', 'cabecalho');
                $tr->addCell('Multa', 'right', 'cabecalho');
                $tr->addCell('Juros', 'right', 'cabecalho');
                $tr->addCell('Honorário', 'right', 'cabecalho'); 
                $tr->addCell('Vlr Projetado', 'right', 'cabecalho');
                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor_lancado = 0;
                $total_geral_valor_projetado = 0;
                $total_geral_multa = 0;
                $total_geral_juros = 0;
                $total_geral_honorario = 0;
                
                $total_valor_lancado = 0;
                $total_valor_projetado = 0;
                $total_multa = 0;
                $total_juros = 0;
                $total_honorario = 0;

                $unidade = '';
                
                // data rows
                foreach ($colunas as $object) // feito pelo select
                {
                    if ($unidade != $object['unidade_id'] ){
                        // totalza a unidades
                        if ( $total_valor_lancado > 0 ) {
                            $tr->addRow();
                            $tr->addCell('Total da unidade :', 'center', 'footer', 5);
                            $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_honorario, 2, ',', '.'), 'right', 'footer');
                            $tr->addCell(number_format($total_valor_projetado, 2, ',', '.'), 'right', 'footer');
                            
                            // zera totalizadores
                            $total_valor_lancado = 0;
                            $total_valor_projetado = 0;
                            $total_multa = 0;
                            $total_juros = 0;
                            $total_honorario = 0;
                    
                        }
                        
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                        
                        $unidade = new Unidade($object['unidade_id']);
                        $proprietario = new Pessoa($unidade->proprietario_id);

                        $tr->addRow();
                        $tr->addCell($unidade->bloco_quadra . ' ' . $unidade->descricao .' - '. $proprietario->nome, 'left', 'normal', 10);
                        
                        $unidade = $object['unidade_id'];
                    }
                
                                    
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object['id'], 'right', $style);
                    $tr->addCell('01'. '/' . $object['mes_ref'], 'center', $style); // 01/01/2023
                    $tr->addCell($object['cobranca'], 'center', $style);
                    
                    $classificacao = new PlanoContas($object['classe_id']);
                                        
                    // verifica se o titulo está pago para colocar observação
                    if ( $object['situacao'] == '0' ) {
                        $tr->addCell($classificacao->descricao, 'left', $style);
                    }else {
                        $tr->addCell($classificacao->descricao . '*', 'left', $style);
                    }
                    
                    $tr->addCell($string->formatDateBR($object['dt_vencimento']), 'center', $style);
                    $tr->addCell(number_format($object['valor'], 2, ',', '.'), 'right', $style);
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object['dt_vencimento']);
                    $time_final = strtotime($data_base);
                    
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($time_inicial . '-' .  $time_final);
                    //return;
                    
                    $juros = $perc_juros * $dias;
                    
                    $juros = (($juros * $object['valor']) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $juros = 0;
                        $object['dias']  = '+'.$dias;
                    }
                    else
                    {
                        $multa = (($perc_multa * $object['valor']) / 100);
                        $object['dias']  = '-'.$dias;
                    }
                    
                    $honorario = (($object['valor'] + $multa + $juros) * $perc_honorario )/100;

                    $tr->addCell(number_format($multa, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($juros, 2, ',', '.'), 'right', $style);
                    $tr->addCell(number_format($honorario, 2, ',', '.'), 'right', $style);
                    
                    $valor_projetado = $object['valor'] + $multa + $juros + $honorario;
                    $tr->addCell(number_format($valor_projetado, 2, ',', '.'), 'right', $style);
                    
                    $total_valor_lancado += $object['valor'];
                    $total_valor_projetado += $valor_projetado;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    $total_honorario += $honorario;
                    
                    // totalizado geral
                    $total_geral_valor_lancado += $object['valor'];
                    $total_geral_valor_projetado += $valor_projetado;
                    $total_geral_multa += $multa;
                    $total_geral_juros += $juros;
                    $total_geral_honorario += $honorario;
                                        
                    $colour = !$colour;
                }
        
                
                // footer row
                $tr->addRow();
                $tr->addCell('Total da unidade:', 'center', 'footer', 5);
                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_honorario, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_valor_projetado, 2, ',', '.'), 'right', 'footer');
                
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('Total Geral:', 'center', 'footer', 5);
                $tr->addCell(number_format($total_geral_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_multa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_juros, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_honorario, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($total_geral_valor_projetado, 2, ',', '.'), 'right', 'footer');
                
                // totaliza por classe_id
                // inclui novos filtros
                $filtro = ' ';
            
            
                if ($formdata->mes_ref)
                {
                  $filtro = $filtro . ' and mes_ref = "' . $formdata->mes_ref.'"';
                
                }
            
                      
                if ($formdata->unidade_id)
                {
                  $filtro = $filtro . ' and unidade_id = ' . $formdata->unidade_id;
                }  
            
                if ($formdata->cobranca)
                {
                  $filtro = $filtro . ' and cobranca = "' . $formdata->cobranca.'"';
                
                }
            
           
                if ($formdata->classe_id)
                {
                  $filtro = $filtro .' and classe_id = ' . $formdata->classe_id;
                }
            
                // incluindo só a partir de 10/2017 ===> "contas_receber.dt_vencimento > '2017-10-01' and " .
                $conn2 = TTransaction::get();
                $sql2 = "SELECT contas_receber.classe_id, plano_contas.descricao, sum( contas_receber.valor ) as valor
                        FROM contas_receber 
                        INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
                        where 
                        contas_receber.condominio_id = " . $condominio_id . " and " .                        
                        "((contas_receber.situacao = 0 and contas_receber.dt_vencimento <= '".$inadimplencia_ate."') or 
                          (contas_receber.situacao = 1 and contas_receber.dt_pagamento > '" . $inadimplencia_ate."' and 
                          contas_receber.dt_vencimento <= '" . $inadimplencia_ate."')or
                        ((select data_base_acordo from acordo where id = contas_receber.numero_acordo) > '" . 
                        $inadimplencia_ate."' and situacao = 2 and contas_receber.dt_vencimento <= '".$inadimplencia_ate."')
                        )";
        
                /*
                SELECT contas_receber.classe_id, plano_contas.descricao, sum( contas_receber.valor ) as valor
                FROM contas_receber 
                INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
                where 
                contas_receber.imovel_id = "1" and 
                ((contas_receber.situacao = 0 and contas_receber.dt_vencimento <= '2017-01-31') or 
                (contas_receber.situacao = 1 and contas_receber.dt_pagamento > '2017-01-31' and 
                contas_receber.dt_vencimento <= '2017-01-31') or
                ((select data_base_acordo from acordo where id = contas_receber.numero_acordo) > '2017-01-31' and 
                situacao = 2 and contas_receber.dt_vencimento <= '2017-01-31'
                ) ) group by contas_receber.classe_id 

                
                */
                
                if ($filtro) {
                  $sql2 = $sql2 . " " . $filtro;
                }
                        
                $sql2 = $sql2 . " group by classe_id";
                
                $colunas2 = $conn2->query($sql2);
                
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('Total ORIGINAL por Classe (sem correções)', 'center', 'footer', 10);
                
                //var_dump($colunas2);
                
                foreach ($colunas2 as $object2) // feito pelo select
                {
                  //var_dump($object2);
                  $tr->addRow();
                  $tr->addCell($object2['descricao'], 'center', 'footer', 5);
                  $tr->addCell(number_format($object2['valor'], 2, ',', '.'), 'right', 'footer', 5);
                
                
                }
                               
                ///////////
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 10);
                
                // stores the file
                if (!file_exists("app/output/ContasReceberInadimp.{$format}") OR is_writable("app/output/ContasReceberInadimp.{$format}"))
                {
                    $tr->save("app/output/ContasReceberInadimp.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberInadimp.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberInadimp.{$format}");
                
                // shows the success message
                //new TMessage('info', 'Report generated. Please, enable popups.');
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
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
}
