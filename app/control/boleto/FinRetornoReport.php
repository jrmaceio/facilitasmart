<?php
/**
 * FinRetornoReport Report
 * @author  <your name here>
 */
class FinRetornoReport extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_FinRetorno_report');
        $this->form->setFormTitle('FinRetorno Report');

        // create the form fields
        $id = new TEntry('id');

        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
        
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', '{id} - {sigla}');
        $id_banco->enableSearch();
        
        $id_banco->setChangeAction(new TAction(array($this, 'onChangeBanco')));
        
        $criteria_ccta = new TCriteria();
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','',$criteria_ccta);

        $id_conta_corrente->setChangeAction(new TAction(array($this, 'onChangeConta')));
        
        $criteria_nr_ret = new TCriteria();
        $criteria_nr_ret->add(new TFilter('id_banco', '<', '0'));
        $numero_retorno = new TDBCombo('numero_retorno', 'facilitasmart', 'FinRetorno', 'id', '{numero_retorno} - {dt_retorno}','',$criteria_nr_ret);

        $criteria_movret = new TCriteria;
        $criteria_movret->add(new TFilter('id', '<', '0'));
        $id_movto_retorno = new TDBCombo('id_movto_retorno', 'facilitasmart', 'TipoMovtoRetorno', 'id', '{codigo} - {descricao}', 'codigo', $criteria_movret);
        $id_movto_retorno->enableSearch();

        $dt_retorno_inicial = new TDate('dt_retorno_inicial');
        $dt_retorno_inicial->setMask('dd/mm/yyyy');
        $dt_retorno_inicial->setDatabaseMask('yyyy-mm-dd');        
        
        $dt_retorno_final = new TDate('dt_retorno_final');
        $dt_retorno_final->setMask('dd/mm/yyyy');
        $dt_retorno_final->setDatabaseMask('yyyy-mm-dd');        
        
        $ordem = new TRadioGroup('ordem');
        $cb = array();
        $cb['1'] = 'Confirmados&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cb['2'] = 'Não Confirmados&nbsp&nbsp&nbsp&nbsp&nbsp';
        $ordem->addItems($cb);
        $ordem->setLayout('horizontal');
        
        $opcao = new TRadioGroup('opcao');
        $cp = array();
        $cp['1'] = 'Retorno&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cp['2'] = 'Retorno + Resumo Movto&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cp['3'] = 'Resumo Movto&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cp['4'] = 'Resumo Clientes&nbsp&nbsp&nbsp&nbsp&nbsp';
        $opcao->addItems($cp);
        $opcao->setLayout('horizontal');

        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Condominio') ], [ $id_condominio ] );
        $this->form->addFields( [ new TLabel('Bco') ], [ $id_banco ] , [ new TLabel('Cta') ], [ $id_conta_corrente ] ); 
        $this->form->addFields( [ new TLabel('Numero Retorno') ], [ $numero_retorno ] , [ new TLabel('Movto Retorno') ] , [ $id_movto_retorno ] );
        $this->form->addFields( [ new TLabel('Dt Retorno Inicial') ] , [ $dt_retorno_inicial ] , [ new TLabel('Dt Retorno Final') ], [ $dt_retorno_final ]);
        $this->form->addFields( [ new TLabel('Opção') ], [ $opcao ] );
        $this->form->addFields( [ new TLabel('Seleção') ], [ $ordem ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $id_condominio->setSize('100%');
        $id_banco->setSize('100%');
        $id_conta_corrente->setSize('100%');
        $numero_retorno->setSize('100%');
        $id_movto_retorno->setSize('100%');
        $dt_retorno_inicial->setSize('100%');
        $dt_retorno_final->setSize('100%');
        $output_type->setSize('100%');


        
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF')); //, 'xls' => 'XLS'));
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

        $obj = new StdClass;        
        
        $obj->id_condominio = TSession::getValue('id_condominio');
        //$obj->id_banco               = 7;
        //$obj->id_conta_corrente      = 1;
        $obj->ordem                  = 2;
        $obj->opcao                  = 2;
        
        TForm::sendData('form_FinRetorno_report', $obj);

        parent::add($container);
    }
    
    /**
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            $string = new StringsUtil;

            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
                
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('FinRetorno');
            $criteria   = new TCriteria;
                   
            if ($data->ordem == 1) { $repositorysegT = new TRepository('FinRetornoSegT'); }
            if ($data->ordem == 2) { $repositorysegT = new TRepository('FinRetornoSegTX'); }
            $criteriasegT   = new TCriteria;
            $criteriasegT->setProperty('order','id_movto_retorno,id_movto_retorno_item,cli_nome');
            $criteriasegT->setProperty('direction','asc');
            
            //$criteria->setProperty('order', '(select id_movto_retorno from fin_retorno_seg_t)');
            
            if ($data->id)
            {
                $criteria->add(new TFilter('id', '=', "{$data->id}"));
            }

            if ($data->id_banco)
            {
                $criteria->add(new TFilter('id_banco', '=', "{$data->id_banco}"));
            }
            if ($data->id_conta_corrente)
            {
                $criteria->add(new TFilter('id_conta_corrente', '=', "{$data->id_conta_corrente}"));
            }
            
            if ($data->numero_retorno)
            {
                $criteria->add(new TFilter('id', '=', "{$data->numero_retorno}"));
            }
            
            if ($data->id_movto_retorno)
            {
                $criteriasegT->add(new TFilter('id_movto_retorno', '=', "{$data->id_movto_retorno}"));
            }

            if ($data->dt_retorno_inicial)
            {
                $criteria->add(new TFilter('dt_retorno', '>=', "{$data->dt_retorno_inicial}"));
            }
            if ($data->dt_retorno_final)
            {
                $criteria->add(new TFilter('dt_retorno', '<=', "{$data->dt_retorno_final}"));
            }

            $objects = $repository->load($criteria, FALSE);
            
            $objectssegT = $repositorysegT->load($criteriasegT, FALSE);
            
            $resumo_movto    = array();
            $resumo_cliente  = array();
            
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(25,80,80,80,80,80,80,80,80,80);
                $widthsa = array(25,80,80,80,80,80,80,80,80,80);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths , $orientation='L');
                        $tr = new TTableWriterPDF($widthsa , $orientation='L');
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
                $tr->addStyle('titlea', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('total', 'Arial', '10', 'B',  '#000000', '#A3A3A3');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('Retorno', 'center', 'header', 10);
                
                // add titles row
                
                if ( ($data->opcao == 1) || ($data->opcao == 2) )
                {
                    $tr->addRow();
                    $tr->addCell('Seq', 'center', 'title');
                    $tr->addCell('Nosso.Num', 'left', 'title');
                    $tr->addCell('Docto', 'left', 'title');
                    $tr->addCell('Titulo', 'left', 'title');
                    $tr->addCell('Forma', 'left', 'title');
                    $tr->addCell('Dt.Vencto', 'left', 'title');
                    $tr->addCell('Dt.Ocor', 'left', 'title');
                    $tr->addCell('Dt.Taxa', 'left', 'title');
                    $tr->addCell('Dt.Credito', 'left', 'title');
                    $tr->addCell('', 'left', 'title');
                }
                
                $tr->addRow();
                $tr->addCell('', 'left', 'titlea');
                $tr->addCell('$Titulo', 'right', 'titlea');
                $tr->addCell('$Taxa', 'right', 'titlea');
                $tr->addCell('$Juros', 'right', 'titlea');
                $tr->addCell('$Descto', 'right', 'titlea');
                $tr->addCell('$Abto', 'right', 'titlea');
                $tr->addCell('$Pago', 'right', 'titlea');
                $tr->addCell('$Credito', 'right', 'titlea');
                $tr->addCell('$Out.Desp', 'right', 'titlea');
                $tr->addCell('$Out.Cred', 'right', 'titlea');
                
                // controls the background filling
                $colour= FALSE;
                
                for ($nivel = 1; $nivel <=3 ; $nivel++) { TSession::setValue('Fin_Retorno_Report_Total_' . $nivel ,null); }
                    
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell( 'Id: ' . str_pad($object->id , 6 , '0' , STR_PAD_LEFT) . 
                        '  Bco: ' . $object->banco->sigla . 
                        '  Cta: ' . $object->conta_corrente->conta . 
                        '  Nr.Retorno: ' . str_pad($object->numero_retorno, 6 , '0' , STR_PAD_LEFT) . 
                        ' Dt.Retorno: ' .  $string->formatDateBR($object->dt_retorno) , 'center', $style , 10);
                    $colour = !$colour;

                    $seq = 1;  $nome_cli_ant = $cnpj_cpf_ant = $id_movto_ret_ant = $id_movto_ret_item_ant = '';
                    // data rows T
                    foreach ($objectssegT as $valueT)
                    {
                        if ($valueT->id_fin_retorno == $object->id)
                        {
                            $movto = ''; if ($valueT->id_movto_retorno > 0) { $movto = 'Movto: ' . $valueT->tipo_movto_retorno->codigo . ' - ' . $valueT->tipo_movto_retorno->descricao; }
                            $movto_item = ''; if ($valueT->id_movto_retorno_item > 0) { $movto_item = 'Ocorrencia: ' . $valueT->tipo_movto_retorno_item->codigo . ' - ' . $valueT->tipo_movto_retorno_item->descricao; }
                            
                            if ( ( $valueT->id_movto_retorno != $id_movto_ret_ant) || ( $valueT->id_movto_retorno_item != $id_movto_ret_item_ant) )
                            {
                                if ( ($data->opcao == 1) || ($data->opcao == 2) )
                                {
                                    $nivel = 3;  $ret_xx = $this->onImprimeTotal( $tr , $nivel ); 
                                    $nivel = 2;  $ret_xx = $this->onImprimeTotal( $tr , $nivel );
                                    $style = $colour ? 'datap' : 'datai';
                                    $tr->addRow();
                                    $tr->addCell( '' , 'left', $style , 10);
                                    $colour = !$colour;
                                    $style = $colour ? 'datap' : 'datai';
                                    $tr->addRow();
                                    $tr->addCell( $movto . '   #   ' . $movto_item  , 'left', $style , 10);
                                    $colour = !$colour;
                                }   
                            }
                            
                            if ($valueT->cli_pfj == 0) { $cnpj_cpf = $valueT->cli_cnpj_cpf; }
                            if ($valueT->cli_pfj == 1) { $cnpj_cpf = Uteis::formataCPF($valueT->cli_cnpj_cpf,'',''); }
                            if ($valueT->cli_pfj == 2) { $cnpj_cpf = Uteis::formataCNPJ($valueT->cli_cnpj_cpf,'',''); }
                            
                            $nome_cli = $valueT->cli_nome;

                            if ( ($cnpj_cpf != $cnpj_cpf_ant) || ($nome_cli != $nome_cli_ant) )
                            {
                                if ( ($data->opcao == 1) || ($data->opcao == 2) )
                                {
                                    $nivel = 3;  $ret_xx = $this->onImprimeTotal( $tr , $nivel ); 
                                    $style = $colour ? 'datap' : 'datai';
                                    $tr->addRow();
                                    $tr->addCell( 'Cliente: ' . $nome_cli . ' - ' . $cnpj_cpf  , 'left', $style , 10);
                                    $colour = !$colour;
                                }
                            }
                            
                            if ( ($data->opcao == 1) || ($data->opcao == 2) )
                            {
                                $style = $colour ? 'datap' : 'datai';
                                $tr->addRow();
                                $tr->addCell($seq, 'center', $style);
                                $tr->addCell($valueT->nosso_numero, 'left', $style);
                                $tr->addCell($valueT->docto, 'left', $style);
                                $tr->addCell(str_pad($valueT->id_contas_receber, 6 , '0' , STR_PAD_LEFT), 'left', $style);
                                $tr->addCell($valueT->forma, 'left', $style);
                                $tr->addCell($string->formatDateBR($valueT->dt_vencto), 'left', $style);
                            }

                            if ($data->ordem == 1)
                            {
                                $objectsU = FinRetornoSegU::where('id_fin_retorno','=',$object->id)
                                        ->where('id_fin_retornosegt','=',$valueT->id)
                                        ->where('id_condominio','=',$object->id_condominio)
                                        ->load();
                            }
                            if ($data->ordem == 2)
                            {
                                $objectsU = FinRetornoSegUX::where('id_fin_retorno','=',$object->id)
                                        ->where('id_fin_retornosegtx','=',$valueT->id)
                                        ->where('id_condominio','=',$object->id_condominio)
                                        ->load();
                            }
                            
                            // data rows U
                            foreach ($objectsU as $valueU)
                            {
                                if ($valueU->id_fin_retorno == $object->id)
                                {
                                    if ( ($data->opcao == 1) || ($data->opcao == 2) )
                                    {
                                        $tr->addCell($string->formatDateBR($valueU->dt_baixa), 'left', $style);
                                        $tr->addCell($string->formatDateBR($valueU->dt_taxa), 'left', $style);
                                        $tr->addCell($string->formatDateBR($valueU->dt_credito), 'left', $style);
                                        $tr->addCell('', 'right', $style);
                                        // -- segunda linha
                                        $style = $colour ? 'datap' : 'datai';
                                        $tr->addRow();
                                        $tr->addCell('', 'right', $style);
                                        $tr->addCell(number_format($valueT->vlr_titulo, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueT->vlr_taxa, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_juros, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_descto, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_abatimento, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_pago, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_credito, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_out_desp, 2, ',', '.'), 'right', $style);
                                        $tr->addCell(number_format($valueU->vlr_out_credito, 2, ',', '.'), 'right', $style);
                                    }
 
                                    $ret_xx = $this->onTotalizador($nivel , $valueU , $valueT );
                                    
                                    // -- montagem resumos --  movto retorno -- //
                                    $dt_cre       = $valueU->dt_credito;
                                    $mov_ret      = $valueT->id_movto_retorno;      if ( ($mov_ret == '') || ($mov_ret == 0) ) { $mov_ret = 0; }
                                    $mov_ret_item = $valueT->id_movto_retorno_item; if ( ($mov_ret_item == '') || ($mov_ret_item == 0) ) { $mov_ret_item = 0; }
                                    $nom_cli      = $valueT->cli_nome;              if ($nom_cli == '') { $nom_cli = 'NAO DEFINIDO'; }
                                    $sex = $dt_cre . '#' . $mov_ret . '#' . $mov_ret_item;
                                    if (!isset($resumo_movto[$sex][1])) { for ($i = 1; $i <=10 ; $i++) { $resumo_movto[$sex][$i] = 0; } }
                                    $resumo_movto[$sex][1]  = $resumo_movto[$sex][1]  + 1;  
                                    $resumo_movto[$sex][2]  = $resumo_movto[$sex][2]  + $valueT->vlr_titulo;
                                    $resumo_movto[$sex][3]  = $resumo_movto[$sex][3]  + $valueT->vlr_taxa;
                                    $resumo_movto[$sex][4]  = $resumo_movto[$sex][4]  + $valueT->vlr_juros;
                                    $resumo_movto[$sex][5]  = $resumo_movto[$sex][5]  + $valueT->vlr_descto;
                                    $resumo_movto[$sex][6]  = $resumo_movto[$sex][6]  + $valueT->vlr_abatimento;
                                    $resumo_movto[$sex][7]  = $resumo_movto[$sex][7]  + $valueT->vlr_pago;
                                    $resumo_movto[$sex][8]  = $resumo_movto[$sex][8]  + $valueT->vlr_credito;
                                    $resumo_movto[$sex][9]  = $resumo_movto[$sex][9]  + $valueT->vlr_out_desp;
                                    $resumo_movto[$sex][10] = $resumo_movto[$sex][10] + $valueT->vlr_out_credito;
                                    // -- montagem resumos --  clientes -- //
                                    $sey = $dt_cre . '#' . $nom_cli;
                                    if (!isset($resumo_cliente[$sey][1])) { for ($i = 1; $i <=10 ; $i++) { $resumo_cliente[$sey][$i] = 0; } }
                                    $resumo_cliente[$sey][1]  = $resumo_cliente[$sey][1]  + 1;  
                                    $resumo_cliente[$sey][2]  = $resumo_cliente[$sey][2]  + $valueT->vlr_titulo;
                                    $resumo_cliente[$sey][3]  = $resumo_cliente[$sey][3]  + $valueT->vlr_taxa;
                                    $resumo_cliente[$sey][4]  = $resumo_cliente[$sey][4]  + $valueT->vlr_juros;
                                    $resumo_cliente[$sey][5]  = $resumo_cliente[$sey][5]  + $valueT->vlr_descto;
                                    $resumo_cliente[$sey][6]  = $resumo_cliente[$sey][6]  + $valueT->vlr_abatimento;
                                    $resumo_cliente[$sey][7]  = $resumo_cliente[$sey][7]  + $valueT->vlr_pago;
                                    $resumo_cliente[$sey][8]  = $resumo_cliente[$sey][8]  + $valueT->vlr_credito;
                                    $resumo_cliente[$sey][9]  = $resumo_cliente[$sey][9]  + $valueT->vlr_out_desp;
                                    $resumo_cliente[$sey][10] = $resumo_cliente[$sey][10] + $valueT->vlr_out_credito;
                                    // --
                                    
                                } // fim  if ($valueU->id_fin_retorno == $object->id)
                            } // fim foreach ($objectsU as $valueU)

                            $colour = !$colour;
                            $seq = $seq + 1;
                            $cnpj_cpf_ant          = $cnpj_cpf;
                            $nome_cli_ant          = $nome_cli;
                            $id_movto_ret_ant      = $valueT->id_movto_retorno; 
                            $id_movto_ret_item_ant = $valueT->id_movto_retorno_item;


                        } // fim  if ($valueT->id_fin_retorno == $object->id)
                        
                    } // fim foreach ($objectsT as $valueT)
                    
                } // fim foreach ($objects as $object)

                // -- impressao final do totalizador -- 
                if ( ($data->opcao == 1) || ($data->opcao == 2) )
                {             
                    for ($nivel = 3; $nivel >=1 ; $nivel--) { $ret_xx = $this->onImprimeTotal( $tr , $nivel ); }
                }


                // -- impressao resumos --
                if ( ($data->opcao == 2) || ($data->opcao == 3) )
                {
                    if ($resumo_movto)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        $tr->addRow();
                        $tr->addCell( 'Resumo Movimento' , 'center', $style , 10);
                        $colour = !$colour;
                        $dt_cre_ant = $mov_ret_ant = $mov_ret_item_ant = '';
                        foreach ($resumo_movto as $key_mov => $value_resumo_movto)
                        {
                            $pieces       = explode("#", $key_mov);
                            $dt_cre       = $pieces[0];
                            $mov_ret      = $pieces[1];
                            $mov_ret_item = $pieces[2];
                            if ( ( $mov_ret != $mov_ret_ant) || ( $mov_ret_item != $mov_ret_item_ant) )
                            {
                                $movto = ''; if ($mov_ret > 0) { $tipo_movto_retorno = 
                                    TipoMovtoRetorno::find($mov_ret,false);  
                                    $movto = 'Movto: ' . str_pad($tipo_movto_retorno->codigo, 3 , '0' , STR_PAD_LEFT) . ' - ' . 
                                    $tipo_movto_retorno->descricao; }
                                $movto_item = ''; if ($mov_ret_item > 0) { $tipo_movto_retorno_item = 
                                    TipoMovtoRetornoItem::find($mov_ret_item,false); 
                                    $movto_item = 'Movto Item: ' . str_pad($tipo_movto_retorno_item->codigo, 3 , '0' , STR_PAD_LEFT)
                                     . ' - ' . $tipo_movto_retorno_item->descricao; }
                                $style = $colour ? 'datap' : 'datai';
                                $tr->addRow();
                                $tr->addCell( 'data credito -> ' . $dt_cre . ' # ' . $movto . ' # ' . $movto_item  , 'left', $style , 10);
                                $colour = !$colour;
                            }
                            // -- segunda linha
                            $style = $colour ? 'datap' : 'datai';
                            $tr->addRow();
                            $tr->addCell(str_pad($value_resumo_movto[1], 3 , '0' , STR_PAD_LEFT), 'center', $style);
                            $tr->addCell(number_format($value_resumo_movto[2], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[3], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[4], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[5], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[6], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[7], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[8], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[9], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_movto[10], 2, ',', '.'), 'right', $style);
                            $dt_cre_ant       = $dt_cre;
                            $mov_ret_ant      = $mov_ret; 
                            $mov_ret_item_ant = $mov_ret_item;
                        }
                    } // fim if ($resumo_movto)
                }

                if ($data->opcao == 4)
                {
                    if ($resumo_cliente)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        $tr->addRow();
                        $tr->addCell( 'Resumo Clientes' , 'center', $style , 10);
                        $colour = !$colour;
                        $dt_cre_ant = $nom_cli_ant = '';
                        foreach ($resumo_cliente as $key_cli => $value_resumo_cliente)
                        {
                            $pieces       = explode("#", $key_cli);
                            $dt_cre       = $pieces[0];
                            $nom_cli      = $pieces[1];
                            if ( $nom_cli != $nom_cli_ant )
                            {
                                $style = $colour ? 'datap' : 'datai';
                                $tr->addRow();
                                $tr->addCell( 'data credito -> ' . $dt_cre . ' # Cliente-> ' . $nom_cli  , 'left', $style , 10);
                                $colour = !$colour;
                            }
                            // -- segunda linha
                            $style = $colour ? 'datap' : 'datai';
                            $tr->addRow();
                            $tr->addCell(str_pad($value_resumo_cliente[1], 3 , '0' , STR_PAD_LEFT), 'center', $style);
                            $tr->addCell(number_format($value_resumo_cliente[2], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[3], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[4], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[5], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[6], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[7], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[8], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[9], 2, ',', '.'), 'right', $style);
                            $tr->addCell(number_format($value_resumo_cliente[10], 2, ',', '.'), 'right', $style);
                            $dt_cre_ant       = $dt_cre;
                            $nom_cli_ant      = $nom_cli;
                        }
                    } // fim  if ($resumo_cliente)        
                }
                

                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s'), 'center', 'footer', 10);
                
                // stores the file
                if (!file_exists("app/output/FinRetorno.{$format}") OR is_writable("app/output/FinRetorno.{$format}"))
                {
                    $tr->save("app/output/FinRetorno.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/FinRetorno.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/FinRetorno.{$format}");
                
                // shows the success message
                new TMessage('info', 'Report generated. Please, enable popups.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            //$this->form->setData($data);
            TForm::sendData('form_FinRetorno_report',$data);
            
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


    public function onTotalizador( $nivel ,  $vlxa , $vlxb )
    {
        $vlr[1]    = 1;
        $vlr[2]    = $vlxb->vlr_titulo;
        $vlr[3]    = $vlxb->vlr_taxa;
        $vlr[4]    = $vlxa->vlr_juros;
        $vlr[5]    = $vlxa->vlr_descto;
        $vlr[6]    = $vlxa->vlr_abatimento;
        $vlr[7]    = $vlxa->vlr_pago;
        $vlr[8]    = $vlxa->vlr_credito;
        $vlr[9]    = $vlxa->vlr_out_desp;
        $vlr[10]   = $vlxa->vlr_out_credito;

        $tot[$nivel] = TSession::getValue('Fin_Retorno_Report_Total_' . $nivel );
        
        if ($nivel > 1)
        {
            for ($i = 1; $i <=10 ; $i++) 
            {
                if (!isset($tot[$nivel][$i])) { $tot[$nivel][$i] = 0; }
                $tot[$nivel][$i] = $tot[$nivel][$i] + $vlr[$i];
            }
        } // fim  if ($nivel > 1)
        TSession::setValue('Fin_Retorno_Report_Total_' . $nivel , $tot[$nivel] );
    }
    
    

    public function onImprimeTotal( $tr , $nivel )
    {
        $tot[$nivel] = TSession::getValue('Fin_Retorno_Report_Total_' . $nivel );
         
        if (!empty($tot[$nivel][1]))
        {
            if ($tot[$nivel][1] > 0)
            {
                $style = 'total';
                $tr->addRow();
                $tr->addCell(str_pad($tot[$nivel][1], 3 , '0' , STR_PAD_LEFT), 'center', $style);
                $tr->addCell(number_format($tot[$nivel][2], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][3], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][4], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][5], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][6], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][7], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][8], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][9], 2, ',', '.'), 'right', $style);
                $tr->addCell(number_format($tot[$nivel][10], 2, ',', '.'), 'right', $style);
            }
        } // fim if (!empty($tot[$nivel][1]))

        if ($nivel > 1)
        {
            $nivelan = $nivel - 1;
            $tot[$nivelan] = TSession::getValue('Fin_Retorno_Report_Total_' . $nivelan );

            for ($i = 1; $i <=10 ; $i++) 
            {
                $len = isset($tot[$nivel][$i]) ? 1 : 0;    if ($len == 0) { $tot[$nivel][$i] = 0; }
                $len = isset($tot[$nivelan][$i]) ? 1 : 0;  if ($len == 0) { $tot[$nivelan][$i] = 0; }
                $tot[$nivelan][$i] = $tot[$nivelan][$i] + $tot[$nivel][$i];
                $tot[$nivel][$i] = 0;
            }
        } // fim if ($nivel > 1)
        
        TSession::setValue('Fin_Retorno_Report_Total_' . $nivel , $tot[$nivel] );
        if ($nivel > 1) { TSession::setValue('Fin_Retorno_Report_Total_' . $nivelan , $tot[$nivelan] ); }
    }



    public static function onChangeBanco($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            if (!empty($param['id_banco']))
            {    
                $criteria = TCriteria::create( ['id_banco' => $param['id_banco'] ] );
                TDBCombo::reloadFromModel('form_FinRetorno_report', 'id_movto_retorno', 'facilitasmart', 'TipoMovtoRetorno', 'id', '({codigo}) {descricao}', 'codigo', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_FinRetorno_report', 'id_movto_retorno');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }


    public static function onChangeConta($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            if (!empty($param['id_conta_corrente']))
            {    
                $criteria = TCriteria::create( ['id_banco' => $param['id_banco'] ] );
                $criteria = TCriteria::create( ['id_conta_corrente' => $param['id_conta_corrente'] ] );
                TDBCombo::reloadFromModel('form_FinRetorno_report', 'numero_retorno', 'facilitasmart', 'FinRetorno', 'id', '{numero_retorno}', 'id', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_FinRetorno_report', 'numero_retorno');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }



}
