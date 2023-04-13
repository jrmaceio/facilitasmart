<?php
/**
 *
 */
class DemonstrativoFinanceiroReportDetalhado extends TPage
{
    protected $form; // form
   ////////// protected $notebook;
    
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
        $this->form->setFormTitle( 'Demonstrativo de Receitas e Despesas Analítico' );
        
        $mostra_fechamento = new THidden('mostra_fechamento');
              
        $this->form->addFields( [new TLabel('')], [$mostra_fechamento]);
        
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        TTransaction::open('facilitasmart');
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        
        foreach ($users as $user)
        {
            if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', $user->condominio_id));
                $criteria->add(new TFilter('status', '=', '1'));
                $criteria->add(new TFilter('mostra_fechamento', '=', 'Y'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência {mes_ref}','mes_ref', $criteria);
        
            }else {
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
                $criteria->add(new TFilter('status', '=', '1'));
                $criteria->add(new TFilter('mostra_fechamento', '=', 'Y'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência  {mes_ref}','mes_ref', $criteria);
        
            } 
            
        }
        TTransaction::close();
        
        $this->form->addFields( [new TLabel('Fechamento')], [$fechamento_id]);
                
        $output_type = new TRadioGroup('output_type');

//        $this->form->addQuickField('Output', $output_type,  100 , new TRequiredValidator);
 
  //      $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
   //     $output_type->setValue('pdf');
    //    $output_type->setLayout('horizontal');
        
        // add the action button
        $this->form->addAction('Gerar', new TAction(array($this,'onGenerator')), 'fa:cog blue');
        
       
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        // add the vbox inside the page
        parent::add($container);
    }
    

    
    function Cabecalho()
    {
        //$this->SetY(5);
        //$this->Cell(0, 10, utf8_decode('NOME DA SUA EMPRESA'),0,0,'C');
    }
        
    /**
     * Generator the report
     */
    function onGenerator($param = NULL)
    {
        try
        {
            
            $string = new StringsUtil;
                      
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $fechamento = new Fechamento($param['fechamento_id']);
            
            //var_dump($param['fechamento_id']);
            //var_dump($fechamento);
            
            $mes_ref = $fechamento->mes_ref;
            $condominio = new Condominio($fechamento->condominio_id);

            $condominio_id = $condominio->id;
            
            $previsao_arrecadacao = $fechamento->previsao_arrecadacao;
            $taxa_inadimplencia = $fechamento->taxa_inadimplencia;
            $dt_fechamento = $fechamento->dt_fechamento;
            $dt_inicial = $fechamento->dt_inicial;
            $dt_final = $fechamento->dt_final;
            $saldo_inicial = $fechamento->saldo_inicial;
            $receita = $fechamento->receita;
            $despesa = $fechamento->despesa;
            $saldo_final = $fechamento->saldo_final;
            $nota_explicativa = $fechamento->nota_explicativa;
          
            $dt_inicial = $fechamento->dt_inicial;
            $dt_final = $fechamento->dt_final;
            
            $conta_fechamento_id = $fechamento->conta_fechamento_id;
            
            /////////////////////////////////////////////////////////////////////////////////////////////////
            // select receitas
            //totalizardor
            $total_receitas = 0;
            $connreceber = TTransaction::get();
            $sqlreceber = "SELECT sum(contas_receber.valor_creditado) as recebimentos
                       FROM contas_receber
                        where  
                        contas_receber.condominio_id = " . $condominio_id . " and " .
                        "contas_receber.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_receber.situacao = 1 and 
                        (contas_receber.dt_liquidacao >= '".$dt_inicial."' and contas_receber.dt_liquidacao <= '".$dt_final."')"
                        ;
            $colunasrecebers = $connreceber->query($sqlreceber);

            foreach ($colunasrecebers as $colunareceber)
            {
                $total_receitas = $colunareceber['recebimentos'];
            }
            
            // fim totalizador
            
            if ($total_receitas == 'NULL' or empty($total_receitas)) {
                $total_receitas = 0.00;
            }
            
            // verifica integridade
            if ($fechamento->receita != $total_receitas) {
                new TMessage('error', 'Erro de integridade, contactar o suporte.');
                      
                // close the transaction
                TTransaction::close();
                return;
            
            }
            
            /*
            SELECT contas_receber.id, contas_receber.condominio_id, contas_receber.mes_ref, 
            contas_receber.classe_id, contas_receber.dt_lancamento, contas_receber.dt_vencimento, 
            contas_receber.valor, contas_receber.descricao, contas_receber.situacao, 
            contas_receber.dt_pagamento, contas_receber.dt_liquidacao, contas_receber.valor_pago, 
            contas_receber.conta_fechamento_id, contas_receber.parcela, plano_contas.codigo as classificacao_codigo, 
            plano_contas.descricao as classificacao_descricao 
            FROM contas_receber 
            INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
            where contas_receber.condominio_id = 6 and 
            contas_receber.situacao = 1 and 
            (contas_receber.dt_liquidacao >= '2017-11-01' and contas_receber.dt_liquidacao <= '2017.11.30')
            */
            
            $connreceber = TTransaction::get();
            $sqlreceber = "SELECT contas_receber.id, 
       contas_receber.condominio_id,
       contas_receber.mes_ref,
       contas_receber.unidade_id,
       contas_receber.classe_id,
       contas_receber.tipo_lancamento,
       contas_receber.dt_lancamento,
       contas_receber.dt_vencimento,
       contas_receber.valor,
       contas_receber.descricao,
       contas_receber.situacao,
       contas_receber.dt_pagamento,
       contas_receber.dt_liquidacao,
       contas_receber.valor_pago,
       contas_receber.valor_creditado,
       contas_receber.conta_fechamento_id,
       contas_receber.parcela, 
       plano_contas.codigo as classificacao_codigo,
       plano_contas.descricao as classificacao_descricao
FROM contas_receber 
INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
where  
                        contas_receber.condominio_id = " . $condominio_id . " and " .
                        "contas_receber.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_receber.situacao = '1' and 
                        (contas_receber.dt_liquidacao >= '".$dt_inicial."' and contas_receber.dt_liquidacao <= '".$dt_final."')"
                        ;
                        
            $sqlreceber = $sqlreceber . " order by classificacao_codigo, dt_liquidacao";
                        
            $colunasreceber = $connreceber->query($sqlreceber);
            
            //var_dump($colunasreceber);
            
            /// fim select receita /////////////////////////////////////////////////////////////////////////////
            
            // select despesas//////////////////////////////////////////////////////////////////////////////////
        
            //totalizardor
            $total_despesas = 0;
            $conn0 = TTransaction::get();
            $sql0 = "SELECT sum(contas_pagar.valor_pago) as pagamentos
                       FROM contas_pagar 
                        where  
                        contas_pagar.condominio_id = " . $condominio_id . " and " .
                        "contas_pagar.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_pagar.situacao = '1' and 
                        (contas_pagar.dt_liquidacao >= '".$dt_inicial."' and contas_pagar.dt_liquidacao <= '".$dt_final."')"
                        ;
            $colunas0 = $conn0->query($sql0);
            foreach ($colunas0 as $coluna0)
            {
                $total_despesas = $coluna0['pagamentos'];
            }
            
            // fim totalizador
            
            $conn = TTransaction::get();
            $sql = "SELECT contas_pagar.id, 
       contas_pagar.condominio_id,
       contas_pagar.mes_ref,
       contas_pagar.classe_id,
       contas_pagar.documento,
       contas_pagar.dt_lancamento,
       contas_pagar.dt_vencimento,
       contas_pagar.valor,
       contas_pagar.descricao,
       contas_pagar.situacao,
       contas_pagar.dt_pagamento,
       contas_pagar.dt_liquidacao,
       contas_pagar.valor_pago,
       contas_pagar.conta_fechamento_id,
       contas_pagar.tipo_pagamento_id,
       contas_pagar.numero_doc_pagamento,
       contas_pagar.parcela, 
       plano_contas.codigo as classificacao_codigo,
       plano_contas.descricao as classificacao_descricao,
       tipo_pagamento.descricao as tipo_pagamento_descricao
FROM contas_pagar 
INNER JOIN tipo_pagamento on contas_pagar.tipo_pagamento_id = tipo_pagamento.id                     
INNER JOIN plano_contas on contas_pagar.classe_id = plano_contas.id 
where  
                        contas_pagar.condominio_id = " . $condominio_id . " and " .
                        "contas_pagar.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_pagar.situacao = '1' and 
                        (contas_pagar.dt_liquidacao >= '".$dt_inicial."' and contas_pagar.dt_liquidacao <= '".$dt_final."')"
                        ;
                        
                       
            $sql = $sql . " order by classificacao_codigo, contas_pagar.descricao";
                        
            $colunas = $conn->query($sql);
            
            // fim select despesas /////////////////////////////////////////////////////////////
            
            //$format  = 'pdf';
            $format  = 'html';
            //var_dump($colunas);
                                       
            if ($colunas)
            {
                $widths = array(100,550,50,50,100);
                
                switch ($format)
                {
                 
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        $break = '<br />';
                        break;
                    case 'pdf':
                        //$tr = new TTableWriterPDF($widths);
                        $tr = new TTableWriterPDF($widths);
                        $fpdf = $tr->getNativeWriter();
                        //$fpdf->setHeaderCallback(array($this,'Cabecalho'));
                        //$this->Cabecalho($fpdf);
                        $tr->setHeaderCallback(array($this, 'Cabecalho'));
                        $break = '<br />';
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        $break = '<br />';
                        break;

                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#000000', '#ffffff');
                $tr->addStyle('cabecalho', 'Arial', '8', 'B',   '#000000', '#ffffff');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '8', '',   '#000000', '#ffffff');
                $tr->addStyle('footer', 'Times', '8', 'B',  '#000000', '#ffffff');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - Gestão Condominial', 'center', 'cabecalho', 5);
                $tr->getNativeWriter();
                $tr->addRow();
                $tr->addCell(utf8_decode('Condominio '.$condominio->resumo), 'center', 'title', 5);
                $tr->addRow();
                $tr->addCell(utf8_decode('DEMONSTRATIVO DE RECEITAS E DESPESAS ANALÍTICO'), 'center', 'title', 5);
                $tr->addRow();
                $tr->addCell('Período de '.$string->formatDateBR($dt_inicial).' a '.$string->formatDateBR($dt_final), 'center', 'title', 5);

                //$tr->addRow();
                //$tr->addCell('Inadimplência até ' . $string->formatDateBR($inadimplencia_ate) . ' para contas emitidas, baixadas e sub judice2', 'left', 'header', 10);
                //$tr->addRow();
                //$tr->addCell('Correção: não aplicada Multa: '.$perc_multa.'% do montante Juros: '.$perc_juros.'% ao dia Data Base: '. $string->formatDateBR($data_base), 'left', 'header', 10);
                //$tr->addRow();
                //$tr->addCell('* Título(s) pago(s) ou em acordo após a data da inadimplência.', 'left', 'header', 10);
                //$tr->addRow();
                //$tr->addCell('Obs.: podem existir contas baixadas ou acordadas que se encontravam em aberto em ' . $string->formatDateBR($inadimplencia_ate), 'left', 'header', 10);
                
                $datahoje = date($dt_inicial);
                $partes = explode("-", $datahoje);
                $ano_hoje = $partes[0];
                $mes_hoje = $partes[1];
                $mes_ant  = ((int) $mes_hoje ) -1;
                
                if ( $mes_ant == 0 ) {
                  $mes_ant = '12';
                  $ano_hoje = $ano_hoje - 1;
                }
                
                $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
                $dia_hoje = $partes[2];
                
                $mesref = $mes_ant . '/' . $ano_hoje; 
                
                //ver essa pesquisa que está buscando so um condominio $fechamento_anterior = Fechamento::where('mes_ref', '=', $mesref);//->
                  //                                 //where('condominio_id', '=', $condominio->id)->load(); 
                
                $sqlFecha = "SELECT * FROM fechamento where  
                        condominio_id = " . $condominio_id . " and " .
                        "mes_ref = '" . $mesref . "'";
                     
                $fechamentos = $conn->query($sqlFecha);
                
                $saldo_anterior = 0;
                //var_dump($fechamentos);
                foreach ($fechamentos as $fechamento) // feito pelo select
                {
                    $saldo_anterior = $fechamento['saldo_final'];
                    
                    //var_dump($fechamento);
                    //var_dump($fechamento['saldo_anterior']);
                    //var_dump($fechamento['despesa']);
                    //var_dump($fechamento['receita']);
                    
                }

                // controls the background filling
                $colour= FALSE;
                
                // add titles row
                $tr->addRow();
                
                // IMPRESSAO DAS RECEITAS  /////////////////////////////////////////////////////////////////
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addCell('Saldo anterior', 'left', 'title', 4);
                //////////// estava buscando errado $tr->addCell('R$ ' . number_format($saldo_anterior, 2, ',', '.'), 'right', 'title');
                $tr->addCell('R$ ' . number_format($saldo_inicial, 2, ',', '.'), 'right', 'title');
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('RECEITAS', 'left', 'cabecalho', 5);
                $colour = !$colour;
                
                $tr->addRow();
                $tr->addCell('Dt Liquidação', 'center', 'cabecalho');
                $tr->addCell('Descrição', 'left', 'cabecalho');
                $tr->addCell('Dia(s)', 'center', 'cabecalho');
                $tr->addCell('%', 'center', 'cabecalho');
                $tr->addCell('Vlr Creditado', 'right', 'cabecalho');
                
                $total_geral_valor_lancado = 0;
                
                $total_valor_lancado = 0;
            
                $classificacao = '';
                $copia_titulo_nivel = '';
                $copia_classificacao = '';
                
                // data rows RECEITAS
                foreach ($colunasreceber as $object) // feito pelo select
                {
                    if ($classificacao != $object['classe_id'] ){
                        // trata os niveis da classe
                        $titulo_classe = $object['classificacao_codigo'];
                        $partes = explode(".", $titulo_classe);
                        //print var_dump($partes);
                        //print '<br />'; 
        
                        // trata até 3 niveis, no futuro podem existir mais niveis
                        $nivel1 = $partes[0]; // 2 despesas
                        $nivel2 = $partes[1]; // 2
                        // RECEITA NÃO EXISTEM 3 NIVEIS ====> $nivel3 = $partes[2]; // 1
                        
                        if ( $copia_titulo_nivel != ($nivel1.'.'.$nivel2) ) {
                            // totalza o nivel
                            if ( $total_valor_lancado > 0 ) {
                                $tr->addRow();
                                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                                $percentual_total = ($total_valor_lancado/$total_despesas)*100;
                                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center','footer');
                                
                                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                                // zera totalizadores
                                $total_valor_lancado = 0;
                            }
                            
                            $titulo_nivels = PlanoContas::where('codigo', '=', $nivel1.'.'.$nivel2)->load();
                                
                            foreach ($titulo_nivels as $titulo_nivel)
                            {
                                $titulo_nivel1 = $titulo_nivel->descricao;
                            }
                                
                            $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                        
                            $tr->addRow();
                            $tr->addCell('   '.$titulo_nivel1, 'left', 'normal', 5);
  
                            $copia_titulo_nivel = $nivel1.'.'.$nivel2;
                        }
                        
                        if ( $object['classificacao_descricao'] != $copia_classificacao ) {
                            $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                            
                            // totalza o nivel
                            if ( $total_valor_lancado > 0 ) {
                                $tr->addRow();
                                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                                $percentual_total = ($total_valor_lancado/$total_receitas)*100;
                                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', 'footer');
                                
                                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                                // zera totalizadores
                                $total_valor_lancado = 0;
                            }                                                
                            $tr->addRow();
                            $tr->addCell('      '.$object['classificacao_descricao'], 'left', 'normal', 5);
                            
                            $copia_classificacao = $object['classificacao_descricao']; 
                        }
                        
                        $classificacao = $object['classe_id'];

                    }
                
                                    
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell('         ' . $string->formatDateBR($object['dt_liquidacao']), 'center', $style);
                    
                    // identifica a uniadde
                    $unidade = new Unidade($object['unidade_id']);
                    $proprietario = new Pessoa($unidade->proprietario_id);
                    
                    if ( $object['tipo_lancamento'] == 'B' ) {
                        $tr->addCell('Movimentação Bancária', 'left', $style);
                    }else {
                       if ( $proprietario->nome == '' ) {
                         $tr->addCell($object['descricao'], 'left', $style);
                       } else {
                         $tr->addCell($unidade->descricao.' - '.$proprietario->nome.', vencimento ['.$string->formatDateBR($object['dt_vencimento']).']', 'left', $style);
                       } 
                    }
                    //$tr->addCell($object['descricao'], 'left', $style);
                    
                    /////////////// desabilitei, nao entendi o que foi tentado fazer    $string->subtrair_datas($object->data_cadastro, $object->data_cancelamento);
                    //dias
                    if ( $object['tipo_lancamento'] == 'B' ) {
                        $tr->addCell(' ', 'left', $style);
                    }else {
                        $tr->addCell($string->subtrair_datas($object['dt_vencimento'], $object['dt_liquidacao']), 'center', $style);
                    }
                    
                    $conta = new PlanoContas($object['classe_id']);
                    
                    // verifica se o titulo está pago para colocar observação
                    //if ( $object['situacao'] == '0' ) {
                    //    $tr->addCell($conta, 'left', $style);
                    //}else {
                    //    $tr->addCell($conta . '*', 'left', $style);
                    //}
                    
                    $percentual_total = ($object['valor_creditado']/$total_receitas)*100;
                    $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', $style);
                    
                    $tr->addCell(number_format($object['valor_creditado'], 2, ',', '.'), 'right', $style);
                    
                    $total_valor_lancado += $object['valor_creditado'];
                    
                    // totalizado geral
                    $total_geral_valor_lancado += $object['valor_creditado'];
                    
                    $colour = !$colour;
                }
        
                
                // footer row
                $tr->addRow();
                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                $percentual_total = ($total_valor_lancado/$total_receitas)*100;
                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', 'footer');
                                
                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                                
                //$tr->addRow();
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addCell('TOTAL DE RECEITAS', 'left', 'footer', 4);
                $tr->addCell(number_format($total_geral_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $total_rec = $total_geral_valor_lancado;
                
                $colour = !$colour;
                
                ///////// FIM DA IMPRESSAO DAS RECEITAS ////////////////////////////////////////////////////
                
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('DESPESAS', 'left', 'cabecalho', 5);
                
                $tr->addRow();
                $tr->addCell('Data Liquidação', 'center', 'cabecalho');
                $tr->addCell('Descrição', 'left', 'cabecalho');
                $tr->addCell('Cheque', 'center', 'cabecalho');
                $tr->addCell('%', 'center', 'cabecalho');
                $tr->addCell('Valor', 'right', 'cabecalho');
                
                // controls the background filling
                $colour= FALSE;
                
                $total_geral_valor_lancado = 0;
                
                $total_valor_lancado = 0;
            
                $classificacao = '';
                $copia_titulo_nivel = '';
                $copia_classificacao = '';
                
                
                // data rows DESPESAS
                foreach ($colunas as $object) // feito pelo select
                {
                    if ($classificacao != $object['classe_id'] ){
                       
                        // trata os niveis da classe
                        $titulo_classe = $object['classificacao_codigo'];
                        $partes = explode(".", $titulo_classe);
                        //print var_dump($partes);
                        //print '<br />'; 
        
                        // trata até 3 niveis, no futuro podem existir mais niveis
                        $nivel1 = $partes[0]; // 2 despesas
                        $nivel2 = $partes[1]; // 2
                        $nivel3 = $partes[2]; // 1
                        
                        if ( $copia_titulo_nivel != ($nivel1.'.'.$nivel2) ) {
                            // totalza o nivel
                            if ( $total_valor_lancado > 0 ) {
                                $tr->addRow();
                                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                                $percentual_total = ($total_valor_lancado/$total_despesas)*100;
                                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', $style);
                                
                                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                                // zera totalizadores
                                $total_valor_lancado = 0;
                            }
                            
                            $titulo_nivels = PlanoContas::where('codigo', '=', $nivel1.'.'.$nivel2)->load();
                                
                            foreach ($titulo_nivels as $titulo_nivel)
                            {
                                $titulo_nivel1 = $titulo_nivel->descricao;
                            }
                                
                            $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                        
                            $tr->addRow();
                            $tr->addCell('   '.$titulo_nivel1, 'left', 'normal', 5);
  
                            $copia_titulo_nivel = $nivel1.'.'.$nivel2;
                        }
                        
                        if ( $object['classificacao_descricao'] != $copia_classificacao ) {
                            $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                            
                            // totalza o nivel
                            if ( $total_valor_lancado > 0 ) {
                                $tr->addRow();
                                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                                $percentual_total = ($total_valor_lancado/$total_despesas)*100;
                                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', $style);
                                
                                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');
                                // zera totalizadores
                                $total_valor_lancado = 0;
                            }                                                
                            $tr->addRow();
                            $tr->addCell('      '.$object['classificacao_descricao'], 'left', 'normal', 5);
                            
                            $copia_classificacao = $object['classificacao_descricao']; 
                        }
                        
                        $classificacao = $object['classe_id'];

                    }
                
                                    
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell('         ' . $string->formatDateBR($object['dt_liquidacao']), 'center', $style);
                    $tr->addCell($object['descricao'], 'left', $style);
                    $tr->addCell($object['numero_doc_pagamento'], 'center', $style);
                    
                    $conta = new PlanoContas($object['classe_id']);
                    
                   
                    $percentual_total = ($object['valor_pago']/$total_despesas)*100;
                    $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', $style);
                    
                    $tr->addCell(number_format($object['valor_pago'], 2, ',', '.'), 'right', $style);
                    
                    
                    $total_valor_lancado += $object['valor_pago'];
                    
                    // totalizado geral
                    $total_geral_valor_lancado += $object['valor_pago'];
                    
                    $colour = !$colour;
                }
        
                
                // footer row
                $tr->addRow();
                $tr->addCell('TOTAL ' . $copia_classificacao, 'right', 'footer', 3);
                                
                $percentual_total = ($total_valor_lancado/$total_despesas)*100;
                $tr->addCell(number_format($percentual_total, 2, ',', '.').'%', 'center', 'footer');
                                
                $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'footer');

                //$tr->addRow();
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addCell('TOTAL DE DESPESAS', 'left', 'footer', 4);
                $tr->addCell('R$ ' . number_format($total_geral_valor_lancado, 2, ',', '.'), 'right', 'footer');
                
                $total_desp = $total_geral_valor_lancado;
                
                $colour = !$colour;
               
                $resultado = $total_receitas-$total_despesas;
                $resultado = 'R$ ' . number_format($resultado, 2, ',', '.');
                
                //if($resultado > 0){
                //     $resultado = "<span style='color:#007BFF'><b>".$resultado."%</b></span>";
                //} else {
                //     $resultado = "<span style='color:#FFB300'><b>".$resultado."%</b></span>";
                //}
                
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('Movimento Líquido do Mês (Receitas-Despesas)', 'left', 'footer', 4);
                $tr->addCell($resultado, 'right', 'footer');
                $colour = !$colour;
                
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('Saldo em ['.$string->formatDateBR($dt_final).']', 'left', 'footer', 4);
                // estava buscando errado $tr->addCell('R$ ' . number_format(($saldo_anterior+$total_receitas)-$total_despesas, 2, ',', '.'), 'right', 'footer');
                $tr->addCell('R$ ' . number_format(($saldo_inicial+$receita)-$despesa, 2, ',', '.'), 'right', 'footer');
                $colour = !$colour;                
              
                ///////////
              
                // grafico receita - despesa
                //$xLabel = $total_rec;
                //$yLabel = $total_desp;
                
                //$var = rand(0, 1000);
                
                //$chart = new TLineChart(new TPChartDesigner);
                //$chart->setTitle('Receita', NULL, NULL);
                //$chart->setSize(1100,600);
                //$chart->setXLabels(['Receita','Despesa']);
                //$chart->setYLabel('R$');
                ////$chart->setOutputPath('app/output/linechart_{$var}.png');
                //$chart->setOutputPath('app/output/linechart_1.png');
                //$chart->addData($xLabel);
                //$chart->addData($yLabel);
                //$chart->generate();
                
                //$tr->addRow();
                ////$tr->addCell(new TImage('app/output/linechart_{$var}.png'), 'center', 'datai', 10);
                //$tr->addCell(new TImage('app/output/linechart_1.png'), 'center', 'datai', 10);
                
                
                // fim grafico
                
                // nota explicativa
                $style = $colour ? 'datap' : 'datai';
                $tr->addRow();
                $tr->addRow();
                $tr->addCell('Nota Explicativa', 'left', 'footer', 5);
                $tr->addRow();
                
                $fpdf->multicell(535, 12, utf8_decode($nota_explicativa), 0, 'left',0); 
                 
                /*            
                if (strlen($nota_explicativa) > 80) {
                    $nota1 = substr($nota_explicativa, 0, 80);
                    $nota2 = substr($nota_explicativa, 80, 160);
                    $nota3 = substr($nota_explicativa, 160, 240);
                    $nota4 = substr($nota_explicativa, 240, 320);
                    
                    $tr->addRow();
                    $tr->addCell(utf8_decode($nota1), 'left', 'footer', 5);
                    $tr->addRow();
                    $tr->addCell('&nbsp;&nbsp;&nbsp;&nbsp;', 'center', 'datai');
                    $tr->addRow();
                    $tr->addCell(utf8_decode($nota2), 'left', 'footer', 5);
                    $tr->addRow();
                    $tr->addCell(utf8_decode($nota3), 'left', 'footer', 5);
                    $tr->addRow();
                    $tr->addCell(utf8_decode($nota4), 'left', 'footer', 5);
                } else {
                    $tr->addCell(utf8_decode($nota_explicativa), 'left', 'footer', 5);
                }
                */
                    
                $colour = !$colour;  
                  
                // footer row
                $tr->addRow();
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 10);
                    
                // sorteia um numero para o relatorio              
                //$var = rand(0, 1000);
                
                // stores the file
                //if (!file_exists("app/output/ContasReceberInadimp_{$var}.{$format}") OR is_writable("app/output/ContasReceberInadimp_{$var}.{$format}"))
                if (!file_exists("app/output/DemonstrativoFinanceiroReport1.{$format}") OR is_writable("app/output/DemonstrativoFinanceiroReport1.{$format}"))
                {
                    //$tr->save("app/output/ContasReceberInadimp_{$var}.{$format}");
                    $tr->save("app/output/DemonstrativoFinanceiroReport1.{$format}");
                }
                else
                {
                    //throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberInadimp_{$var}.{$format}");
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/DemonstrativoFinanceiroReport1.{$format}");
                }
                
                // open the report file
                //parent::openFile("app/output/ContasReceberInadimp_{$var}.{$format}");
                parent::openFile("app/output/DemonstrativoFinanceiroReport1.{$format}");
                                
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
