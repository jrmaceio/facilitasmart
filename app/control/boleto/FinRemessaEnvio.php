<?php
class FinRemessaEnvio extends TPage
{
    protected $form;
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');

        //$unit_erp = TSession::getValue('cliente_ERP');
        //$unit_emp = TSession::getValue('userempresa');
        
        $this->form = new BootstrapFormBuilder('form_FinRemessaEnvio');
        $this->form->setFormTitle('Remessa Envio');

        // cria os campos do formularios
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);

        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', '{id} - {sigla}');
        $id_banco->enableSearch();
        
        $id_banco->setChangeAction(new TAction(array($this, 'onChangeBanco')));
        
        $criteria_cta_corrente = new TCriteria();        
        //$criteria_cta_corrente->add(new TFilter('id_cliente_erp','=',$unit_erp));
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', '{id} - {conta} - {agencia} - {titular}','',$criteria_cta_corrente);
        
        $id_layout_cnab = new TRadioGroup('id_layout_cnab');
        $id_layout_cnab->addItems(array('240' => '240', '400' => '400'));
        $id_layout_cnab->setLayout('horizontal');
        
        $numero_remessa = new TEntry('numero_remessa');

        $caminho      = new TEntry('caminho');
        
        $arquivo      = new TEntry('arquivo');
        
        $id_conta_corrente->setChangeAction(new TAction(array($this, 'onBuscaCaminho')));
		
		$numero_remessa->setExitAction(new TAction(array($this, 'onVerificaExporta')));
        
        
        // define os tamanhos

        $this->form->addFields( [new TLabel('Condominio')], [$id_condominio] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] );
        $this->form->addFields( [new TLabel('Conta Corrente')], [$id_conta_corrente] );
        $this->form->addFields( [new TLabel('Layout Cnab')], [$id_layout_cnab] );
        $this->form->addFields( [new TLabel('Numero Remessa')], [$numero_remessa] );
        $this->form->addFields( [new TLabel('Caminho')], [$caminho] );
        $this->form->addFields( [new TLabel('Arquivo')], [$arquivo] );
        
        // size
        $id_condominio->setSize('100%');
        $id_banco->setSize('100%');
        $id_conta_corrente->setSize('100%');
        $id_layout_cnab->setSize('100%');
        $numero_remessa->setSize('100%');
        $caminho->setSize('100%');
        $arquivo->setSize('100%');
        
        


        $panel = new TPanelGroup;
        //$panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );

        $btn = $this->form->addAction('Gera',  new TAction([$this, 'onGera']), 'ico_apply.png');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
        //$this->form->addAction(_t('Back'),new TAction(array('FinRemessaList','onReload')),'far:arrow-alt-circle-left red');


        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        $obj = new StdClass;        
        $obj->id_layout_cnab = 240;
        
        TForm::sendData('form_FinRemessaEnvio', $obj);
        
        parent::add($container);
    }
    
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }


    public function onGera( $param )
    {
        try
        {
            $data = $this->form->getData();
            
            $arq_cam  = $data->caminho;
            $arq_plan = $data->arquivo;
    		
            // -- validacao da pasta -- //
            if ( !is_dir( $arq_cam ) ) { throw new Exception('Pasta nao existe !!!'); }

    		set_time_limit(0);
    
            TTransaction::open('facilitasmart');
            
            //criando log 
            //TTransaction::setLogger(new TLoggerTXT('tmp/log.txt'));
    
            $nrrem = $lotser = $nrcontrole = $seqlote = $seqlotetot = $totreg = $nrseqarq = $oks = 0;
            
            $reg_fin_remessa = FinRemessa::where('id_condominio', '=', $data->id_condominio)
                                        ->where('id_banco', '=', $data->id_banco)
                                        ->where('id_conta_corrente', '=', $data->id_conta_corrente)
                                        ->where('id_layout_cnab', '=', $data->id_layout_cnab)
                                        ->where('numero_remessa', '=', $data->numero_remessa)
                                        ->load();
    
            foreach ($reg_fin_remessa as $value_reg_fin_remessa)
            {
    
                // inicio reg 0
                $nrseqarq = $value_reg_fin_remessa->numero_remessa;
                // definicoes //
                
                $id_movrem = $value_reg_fin_remessa->id_movto_remessa;
                if ($id_movrem == '')
                {
                    $reg_tpmovrem = TipoMovtoRemessa::where('id_banco', '=', $data->id_banco)->where('codigo', '=', '01')->load();
                    foreach ($reg_tpmovrem as $value_reg_tpmovrem)
                    {
                        $id_movrem = $value_reg_tpmovrem->id;
                        if ($id_movrem != '') { break; }
                    }
                }
                
                $codmovrem       = TipoMovtoRemessa::find($id_movrem)->codigo;
                $tipocarteira    = $value_reg_fin_remessa->carteira;
                $codprotesto     = $value_reg_fin_remessa->codigo_protesto;  // 1-protesto /3-nao protesto /8-negativa sem protesto /9-cancel protesto
                $nrdiasprotesto  = $value_reg_fin_remessa->dias_protesto;  // 05 ou 00
                $codbxdevol      = $value_reg_fin_remessa->codigo_baixa_devolucao;
                $nrdiasbxdevol   = $value_reg_fin_remessa->dias_baixa_devolucao;
                if ($data->id_banco == 7) { if ($nrdiasbxdevol != 60) { $nrdiasbxdevol = 60; } }
                // definicoes //
                
                // pegar dados da empresa
                $reg_cliente_erp = ''; //ServCliErpEmp::BuscaCliErp( $data->id_cliente_erp );
                $reg_empresa     = ''; //ServCliErpEmp::BuscaEmpresa( $data->id_cliente_erp , $data->id_empresa );
                
                $reg_empresa = new Condominio($data->id_condominio);
    
                $tprg = 0;
                $totreg = $totreg + 1;
                $ret_lay = $this->onBuscaLayout( 'A' , $value_reg_fin_remessa->id_banco  , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , '' , $seqlote , $codmovrem , $tipocarteira , '' , '' , '' , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , '' , '' ); 
                
                if ($oks == 0)
                {
                    $oks = 1;
                    $ret_arquivo = $this->onOpen( $arq_cam , $arq_plan );
                    $oks = 2;
                }
                $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                $lotser = $lotser + 1;
                $seqlotetot = $seqlotetot + 1;
                $totreg = $totreg + 1;
                $tprg = 1;
                
                $ret_lay = $this->onBuscaLayout( 'A' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , '' , $seqlote , $codmovrem , $tipocarteira , '' , '' , '' , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , '' , '' );
                $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                // fim reg 0
                
                // busca itens da remessa
                $reg_fin_remessa_item = FinRemessaItem::where('id_condominio', '=', $data->id_condominio)
                                            ->where('id_fin_remessa', '=', $value_reg_fin_remessa->id)
                                            ->load();
    
                foreach ($reg_fin_remessa_item as $value_reg_fin_remessa_item)
                {
                    $reg_fin_titulo = new ContasReceber($value_reg_fin_remessa_item->id_contas_receber);
    
                    // -- verifica saldo titulo -- //
                    if ($reg_fin_titulo->situacao == 0) 
                    { 
                        $docto = $reg_fin_titulo->id; //mes_ref;  // <<-- trocado conforme Junior solicitou //
                        $dt_base = Uteis::formataDataIngles(date("d/m/Y"),'','');
                        
                        $unidade = new Unidade($reg_fin_titulo->unidade_id);
                        $reg_pessoa = new Pessoa($unidade->proprietario_id);
                        $vlr_saldo = $reg_fin_titulo->valor;
                        $valor_cotacao = '1';
                        $rx[$tprg] = '';
                        $nossonumero = $reg_fin_titulo->nosso_numero; // busca nosso numero já atribuido (caso ja exista)
                        
                        // se nossonumero for vazio (caso nao tenha) vai pegar sequencial novo
                        if ($nossonumero == '')
                        {
                            $reg_cta_nossonr_gravado = ContaCorrenteNossoNumero::where('id_condominio', '=', $data->id_condominio)
                                                                    ->where('id_conta_corrente', '=', $data->id_conta_corrente)
                                                                    ->load();
        
                            $reg_cta_nossonr_gravado_reverse = array_reverse($reg_cta_nossonr_gravado); //inverte tabela 
                            $nseq = $ct = 0;
                            foreach ($reg_cta_nossonr_gravado_reverse as $value) 
                            {
                               if ($ct == 0) { $nseq = $value->sequencial + 1; }
                               $ct = $ct + 1;
                            } // fim foreach ($reg_cta_nossonr_gravado_reverse as $value)
                            if ($nseq == 0) { $nseq = 1; }
                            $reg_cta_nossonr = new ContaCorrenteNossoNumero;
                            
                            $reg_cta_nossonr->id_condominio     = $data->id_condominio;
                            $reg_cta_nossonr->id_conta_corrente = $data->id_conta_corrente;
                            $reg_cta_nossonr->sequencial        = $nseq;
                            $reg_cta_nossonr->id_contas_receber = $value_reg_fin_remessa_item->id_contas_receber;
                            
                            $reg_cta_nossonr->store(); 
        
                            // -- banco sicredi id 07 -- //
                            if ($value_reg_fin_remessa->id_banco == 7)
                            {
                                $tpcar = 1;
                                $codcart = 1;
                                $tmseq = 6;
                                if ($value_reg_fin_remessa->carteira == 2)
                                {
                                    $tpcar = 1;
                                    $nux = Uteis::numeroEsquerda($nseq,$tmseq-1);
                                }
                                
                                $agencia = Uteis::numeroEsquerda( ContaCorrente::find( $data->id_conta_corrente )->agencia , 4); 
        
                                $explode = explode("-", ContaCorrente::find( $data->id_conta_corrente )->conta );
                                $conta               = Uteis::numeroEsquerda( $explode[0] , 5);
                                
                                $byteidt = 2;  //$byteidt = $dadosboleto["byte_idt"];
                                $inicio_nosso_numero  = 20;
                                $convenio = Uteis::numeroEsquerda( ContaCorrente::find( $data->id_conta_corrente )->convenio , 5);
                                $posto = Uteis::numeroEsquerda( ContaCorrente::find( $data->id_conta_corrente )->posto , 2);
                                $nnum = $inicio_nosso_numero . $byteidt . Uteis::numeroEsquerda( $nseq , 5);
                                //calculo do DV do nosso número
                                $dv_nosso_numero = $this->sicredi_dv_nossonumero("$agencia$posto$convenio$nnum");
                                //$dv_nosso_numero = $this->sicredi_dv_nossonumero("$agencia$posto$conta$nnum");
                                $nossonumero_dv ="$nnum$dv_nosso_numero";
                                $nossonumero = substr($nossonumero_dv,0,2).'/'.substr($nossonumero_dv,2,6).'-'.substr($nossonumero_dv,8,1);
                                $reg_return = FinTituloService::geraNossoNumero( $value_reg_fin_remessa_item->id_contas_receber , $nossonumero , $data->id_banco , $data->id_conta_corrente );
                            }
                            
                            // -- banco sicoob id 08 -- //
                            if ($value_reg_fin_remessa->id_banco == 8)
                            {
                                $tpcar = 1;
                                $codcart = 1;
                                $tmseq = 6;
                                if ($value_reg_fin_remessa->carteira == 2)
                                {
                                    $tpcar = 1;
                                    $nux = Uteis::numeroEsquerda($nseq,$tmseq-1);
                                }
                                
                                $agencia     = Uteis::numeroEsquerda( ContaCorrente::find( $data->id_conta_corrente )->agencia , 4); 
                                $convenio10  = Uteis::numeroEsquerda( ContaCorrente::find( $data->id_conta_corrente )->convenio , 10);
        
                                $explode = explode("-", ContaCorrente::find( $data->id_conta_corrente )->conta );
                                $conta               = Uteis::numeroEsquerda( $explode[0] , 5);
                                
                                $inicio_nosso_numero  = 20;
                                
                                $nnum = $agencia . $convenio10 . Uteis::numeroEsquerda( $nseq , 7);
                                //calculo do DV do nosso número
                                $dv_nosso_numero = $this->sicoob_dv_nossonumero($nnum);
                                $nossonumero_dv = Uteis::numeroEsquerda($nseq,7) . $dv_nosso_numero;
                                $nossonumero = substr($nossonumero_dv,0,7).'-'.substr($nossonumero_dv,7,1);
                                $reg_return = FinTituloService::geraNossoNumero( $value_reg_fin_remessa_item->id_contas_receber , $nossonumero , $data->id_banco , $data->id_conta_corrente );
                            }
                            
                        } // fim if ($nossonumero == '')
                        
                                        
                        if ($value_reg_fin_remessa->id_banco == 7)
                        {
                            $iproduto = preg_replace('/[^0-9]/', '', $nossonumero); //$iproduto = $nossonumero;
                        }
                        if ($value_reg_fin_remessa->id_banco == 8)
                        {
                            $iproduto = $nossonumero;
                        }
                        // --
                        $seqlote = $seqlote + 1;
                        $seqlotetot = $seqlotetot + 1;
                        $totreg = $totreg + 1; 
                        $tprg = 3;
                        
                        $ret_lay = $this->onBuscaLayout( 'P' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , $reg_pessoa , $reg_fin_titulo );
                        $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                        $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                            
                        $seqlote = $seqlote + 1;
                        $seqlotetot = $seqlotetot + 1;
                        $totreg = $totreg + 1;
                        $tprg = 3;
                        
                        $ret_lay = $this->onBuscaLayout( 'Q' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , $reg_pessoa , $reg_fin_titulo );
                        $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                        $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                
                        $seqlote = $seqlote + 1;
                        $seqlotetot = $seqlotetot + 1;
                        $totreg = $totreg + 1;
                        $tprg = 3;
                        
                        $ret_lay = $this->onBuscaLayout( 'R' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , $reg_pessoa , $reg_fin_titulo );
                        $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                        $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
    
                        /*
                        --> desativado conforme orientacao do SICREDI - Alagoas em 21/10/2020  <---
                        $seqlote = $seqlote + 1;
                        $seqlotetot = $seqlotetot + 1;
                        $totreg = $totreg + 1;
                        $tprg = 3;
                        
                        $ret_lay = $this->onBuscaLayout( 'S' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , $reg_pessoa , $reg_fin_titulo );
                        $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                        $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                        */
    
                    } // fim  if ($reg_fin_titulo->situacao == 0)
                                
                } // fim foreach ($reg_fin_remessa_item as $value_reg_fin_remessa_item)
            
            } // fim foreach ($reg_fin_remessa as $value_reg_fin_remessa)
            
            // -- finalizacao
            if ($totreg > 0)
            {
                $seqlotetot = $seqlotetot + 1;
                $totreg = $totreg + 1;
                $tprg = 5;
    
                $ret_lay = $this->onBuscaLayout( 'A' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser  , $nrseqarq , '' , $seqlote , $codmovrem , $tipocarteira , '' , '' , '' , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , '' , '' );
                $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );            
                $tprg = 9;
                $rx[$tprg] = '';
                $totreg = $totreg + 1;
                $ret_lay = $this->onBuscaLayout( 'A' , $value_reg_fin_remessa->id_banco , $value_reg_fin_remessa->id_conta_corrente , $tprg , $lotser  , $nrseqarq , '' , $seqlote , $codmovrem , $tipocarteira , '' , '' , '' , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg , '' , '' );
                $ret_prx = $this->onPrxLinha( $ret_arquivo ); 
                $ret_sem = $this->onGrava( $ret_arquivo , $ret_lay );
                
                // -- ultima linha em branco final do arquivo -- //
                $ret_prx = $this->onPrxLinha( $ret_arquivo );
                
                if ($oks == 2)
                {
                    $ret_prx = $this->onFecha( $ret_arquivo ); 
                    $oks = 3;
                    //$ret_prx = $this->onCopia( $ret_arquivo ); //D 4300
                }
    
                $xx_reg_finremessa = FinRemessa::find($value_reg_fin_remessa->id,false);
                $xx_reg_finremessa->caminho = $arq_cam;
                $xx_reg_finremessa->arquivo = $arq_plan;
                $xx_reg_finremessa->store();
                
                
                print "<br>";
                print $ret_prx;

                //S ^CRREMESSA02(EMP,BCO,CTA,NRREM)=$H
                new TMessage('info', 'Arquivo gerado com sucesso !!!');

                // faz download do arquivo gerado  - precisa resolve o problema da extensão do arquivo download.php
                TPage::openFile($arq_cam . $arq_plan);
            }else
            {
                new TMessage('info', 'Arquivo NÂO gerado  !!!');
            }
    		
            TTransaction::close(); // close transaction
        
        } // fim try
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
        
    }



    function onBuscaLayout( $segui , $idbco , $idcta , $tprg , $lotser , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg  , $reg_pessoa , $reg_fin_titulo )
    {
        $ret_layout = '';  $tpt = 1;  $pdarq = 240;  $rmrt = 1;  $rx[$tprg] = '';
        $reg_layout_cnab = LayoutCnab::where('tipo_transacao', '=' , $tpt)
                                     ->where('padrao_arquivo', '=', $pdarq)
                                     ->where('id_banco', '=', $idbco)
                                     ->where('remesa_retorno', '=', $rmrt)
                                     ->where('tipo_registro', '=', $tprg)
                                     ->where('seguimento', '=', $segui)
                                     ->where('id_banco', '=', $idbco)
                                     ->load();
                                     
        $reg_layout_cnab_asort = asort($reg_layout_cnab);
        
        foreach ($reg_layout_cnab as $value_reg_layout_cnab)
        {
            $ret_linha = $this->onMontaLinha( $value_reg_layout_cnab , $idbco , $idcta , $lotser  , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg  , $reg_pessoa , $reg_fin_titulo );
            $rx[$tprg] = $rx[$tprg] . $ret_linha;
        }
        return $rx[$tprg];
    }

    function onMontaLinha( $layout , $id_banco_bd , $id_cta_bd , $lotser  , $nrseqarq , $iproduto , $seqlote , $codmovrem , $tipocarteira , $docto , $vlr_saldo , $valor_cotacao , $codprotesto , $nrdiasprotesto , $codbxdevol , $nrdiasbxdevol , $seqlotetot , $totreg  , $reg_pessoa , $reg_fin_titulo )
    {
        $dtatual = date("Y-m-d");
        $hratual = date('H:i:s');
        $codformcad     = 1;     //    1 = Com Cadastramento (Cobrança Registrada)
        $codtpdoc       = 2;     //    2 = Escritural
		$codesptit      = 03;    //   12 = NP / 03 = DMI Duplicata Mercantil por indicacao
		$codjrsmora     = 1;     //	   1 = Valor por Dia
        $tpremessa      = '';    //     vazio
        
        
        if ($layout->comando != '')
        {
            $cmd = $layout->comando;
            $cmdx = eval($cmd); // executa por indirecao
            
            if ($layout->formato == 'V')
            {
                $pf = 2; //$layout->padrao; // casas decimais $pf = 2; //  criar campo na tabela de casas decimais //$layout->padrao; // casas decimais 
                $sx = Uteis::numeroBrasil($nux, $pf);  
                $nux = trim($sx);
                $layout->formato = 'N';
                $nux = preg_replace('/[^0-9]/', '', $nux);
            }
            if ($layout->formato == 'N')
            {
                $nux = preg_replace('/[^0-9]/', '', $nux);
                $nux = (int)$nux;
                $ux = $layout->posicao_total;
                $sx = Uteis::numeroEsquerda($nux , $ux);
            }
            if ($layout->formato == 'B')
            {
                if ($nux == 0) 
                {
                    $nux = '00000000';
                }else
                {
                    $explode = explode("-",$nux);
                    $nux = $explode[2] . $explode[1] . $explode[0];
                }
                $sx = $nux;
            }
            if ($layout->formato == 'A')
            {
                $sx = $this->sanitizeString($nux);
                $ini = strlen($sx) + 1; 
                $sx = substr($sx , 0 , $layout->posicao_total);
                if ($ini == 0)
                {
                    $ini = 1;
                }
                for ($ini ; $ini<=$layout->posicao_total ; $ini++)
                {
                    $sx = $sx . ' ';
                }
		    }
        } // fim if ($layout->comando != '')
        
        if ($layout->comando == '')
        {
            $nux = trim($layout->padrao);
            if ($layout->formato == 'V')
            {
                $pf = 2; //  criar campo na tabela de casas decimais //$layout->padrao; // casas decimais 
                $sx = Uteis::numeroBrasil($nux, $pf);  
                $nux = trim($sx);
                $layout->formato = 'N';
                $nux = preg_replace('/[^0-9]/', '', $nux);
            }
            if ($layout->formato == 'N')
            {
                $nux = preg_replace('/[^0-9]/', '', $nux);
                $nux = (int)$nux;
                $ux = $layout->posicao_total;
                $sx = Uteis::numeroEsquerda($nux , $ux);
            }
            if ($layout->formato == 'B')
            {
                if ($nux == 0) 
                {
                    $nux = '00000000';
                }else
                {
                    $explode = explode("-",$nux);
                    $nux = $explode[2] . $explode[1] . $explode[0];
                }
                $sx = $nux;
            }
            if ($layout->formato == 'A')
            {
                $sx = $nux;
                $ini = strlen($sx) + 1;  
                if ($ini == 0)
                {
                    $ini = 1;
                }
                for ($ini ; $ini<=$layout->posicao_total ; $ini++)
                {
                    $sx = $sx . ' ';
                }
		    }
		} // fim if ($layout->comando == '')
		return $sx;
    }
    
    function sanitizeString($str) 
    {
        $str = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($str)));
        return $str;
    }
    
    function PegaNrAge( $id_cta_bd )
    {
        $explode = explode("-", ContaCorrente::find($id_cta_bd)->agencia );
        $nr_age = $explode[0];
        return $nr_age;
    }
    
    function PegaNrCta( $id_cta_bd )
    {
        $explode = explode("-", ContaCorrente::find($id_cta_bd)->conta );
        $nr_cta = $explode[0];
        return $nr_cta;
    }

    function PegaDvCta( $id_cta_bd )
    {
        $explode = explode("-", ContaCorrente::find($id_cta_bd)->conta );
        $dv_cta = $explode[1];
        return $dv_cta;
    }
    
    function ConverteData( $dtconv )
    {
        if ($dtconv == '') { $dtconv = '0000-00-00'; }
        $nux = $dtconv;
        $explode = explode("-",$nux);
        $nux = $explode[2] . $explode[1] . $explode[0];
        return $nux;
    }

    function ConverteHora( $hrconv )
    {
        $nux = $hrconv;
        $explode = explode(":",$nux);
        $nux = $explode[0] . $explode[1] . $explode[2];
        return $nux;
    }
        	
    function onOpen( $arq_cam , $arq_plan )
    {
        $arq_sicom = fopen($arq_cam . $arq_plan,"w+");
        return $arq_sicom;
        //OPEN CAM:"WNS":1 I $T=0 W /CUP(7,16)," NÃO pude Criar Arquivo! " H 1
    }

    function onGrava( $arq_sicom , $linha )
    {
        fwrite($arq_sicom, $linha);
        $grava = '';
        //USE CAM ;:1 I $T=0 W /CUP(7,16)," NÃO pude usar Arquivo. " H 1
        //W RX(TPRG) ;,$C(13),! ;,$C(10) ;,!
        //USE 0
        return $grava;
    }

    function onPrxLinha( $arq_sicom )
    {
        fwrite($arq_sicom,"\r\n");
        $grava = '';
        //USE CAM ;:1 I $T=0 W /CUP(7,16)," NÃO pude usar Arquivo! abre linha " H 1
        //;W !
        //W $C(13),$C(10)
        //USE 0
        return $grava;
    }

    function onFecha( $arq_sicom )
    {
        fclose($arq_sicom);
        $grava = '';
        //USE CAM
        //W !
        ////W $C(13)
        //USE 0
        //CLOSE CAM
        //W /CUP(7,16)," Transação Efetuada com SUCESSO !!! " H 1
        return $grava;
    }
    
    function onCopia( $arq_sicom )
    // 4300    
    {
        //S XB=$ZF(-1,"cp "_CAM_" "_CAMDES)
    }
    
    
    public static function onVerificaExporta( $param )
    {
        $numero_remessa = $param['numero_remessa'];

        TTransaction::open('facilitasmart');
        $reg_rem = FinRemessa::where('id_condominio', '=', $param['id_condominio'])
                                ->where('id_banco', '=', $param['id_banco'])
                                ->where('id_conta_corrente', '=', $param['id_conta_corrente'])
                                ->where('numero_remessa', '=', $param['numero_remessa'])
                                ->load();
                                
        foreach ($reg_rem as $value_reg_rem)
        {
            if ($value_reg_rem->arquivo != '')
            {
                new TMessage('info','Arquivo de Remessa nr '. Uteis::numeroEsquerda($param['numero_remessa'],4) . '<br>Gerado em: ' . $value_reg_rem->caminho . $value_reg_rem->arquivo );
                //throw new Exception('Orçamento já possui contrato !!!');
            }
        }
        
        TTransaction::close();
    
    }  // fim  public static function onVerificaExporta( $param )

    
    public static function onBuscaCaminho( $param )
    {
        $dtatual = date("Y-m-d");
        $explode = explode("-",$dtatual);
        $dia = $explode[2];
        $mes = $explode[1];
        $ano = $explode[0];
        
        // --
        $reg_cta = '';
        TTransaction::open('facilitasmart');
        if (!empty($param['id_conta_corrente']))
        {
            $reg_cta = new ContaCorrente($param['id_conta_corrente']);
            $explode = explode("-", $reg_cta->conta );
            $ctax = $explode[0];
            $convx = $reg_cta->convenio;
        }
        

        $reg_rem = FinRemessa::where('id_condominio', '=', $param['id_condominio'])
                                ->where('id_banco', '=', $param['id_banco'])
                                ->where('id_conta_corrente', '=', $param['id_conta_corrente'])
                                ->where('dt_emissao', '=', $dtatual)
                                ->load();
    
        // sicredi - sicoob
        if ( ($param['id_banco'] == 7) || ($param['id_banco'] == 8) )
        { 
            if ($mes <= '09') { $mes = (int)$mes; }
            if ($mes == '10') { $mes = 'O'; }
            if ($mes == '11') { $mes = 'N'; }
            if ($mes == '12') { $mes = 'D'; }
            //$arquivo = Uteis::numeroEsquerda($reg_cta->convenio,5) . $mes . $dia;
            $arquivo = Uteis::numeroEsquerda($convx,5) . $mes . $dia;  //$arquivo = Uteis::numeroEsquerda($ctax,5) . $mes . $dia;
        }

        // sicoob -- gerar extensao do arquivo --
        if ( $param['id_banco'] == 8 )
        {
            $ext = 0;  foreach ($reg_rem as $value_reg_rem) { $ext = $ext + 1; }
            if ($ext == 0) { $ext = 1; }
            $arquivo = $arquivo . "." . Uteis::numeroEsquerda($ext,3);
        }
                
        // sicredi -- gerar extensao do arquivo --
        if ( $param['id_banco'] == 7 )
        {
            $ext = 'CRM';  $context = 0;
            if ($reg_rem) { foreach ($reg_rem as $value_reg_rem) { $context++; } }
            if ($context > 0) { $ext = 'CR' . $context; }
            $arquivo = $arquivo . "." . $ext;
        }
        
        TTransaction::close();
        
        $obj = new StdClass; 
        $obj->caminho = $reg_cta->arq_remessa;
        $obj->arquivo = $arquivo;
        TForm::sendData('form_FinRemessaEnvio', $obj);
    }  // fim  public static function onBuscaCaminho( $param )

    

    // -- dv sicredi -- //
    function sicredi_dv_nossonumero($numero) {
    	$resto2 = $this->sicredi_modulo_11($numero, 9, 1);
    	// esta rotina sofrer algumas alterações para ajustar no layout do SICREDI
    	 $digito = 11 - $resto2;
         if ($digito > 9 ) {
            $dv = 0;
         } else {
            $dv = $digito;
         }
    return $dv;
    }

    // -- dv sicoob -- //
    function sicoob_dv_nossonumero($numero) 
    {
        $cont=0;
        $calculoDv = '';
     	for($num=0;$num<=strlen($numero);$num++)
    	{
    	    // constante fixa Sicoob » 3197
    		$cont++;
    		if($cont == 1)
    		{
    			$constante = 3;
    		}
    		if($cont == 2)
    		{
    			$constante = 1;
    		}
    		if($cont == 3)
    		{
    			$constante = 9;
    		}
    		if($cont == 4)
    		{
    			$constante = 7;
    			$cont = 0;
    		}
    		$calculoDv = $calculoDv + (substr($numero,$num,1) * $constante);
    	}
        $Resto = $calculoDv % 11;
        $dv = 11 - $Resto;
        if ($dv == 0) $dv = 0;
        if ($dv == 1) $dv = 0;
        if ($dv > 9) $dv = 0;
        return $dv;
    }
    
    
      
    function sicredi_modulo_11($num, $base=9, $r=0)  {
        $soma = 0;
        $fator = 2;
    
        /* Separacao dos numeros */
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num,$i-1,1);
            // Efetua multiplicacao do numero pelo falor
            $parcial[$i] = $numeros[$i] * $fator;
            // Soma dos digitos
            $soma += $parcial[$i];
            if ($fator == $base) {
                // restaura fator de multiplicacao para 2 
                $fator = 1;
            }
            $fator++;
        }
    
        /* Calculo do modulo 11 */
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;
            return $digito;
        } elseif ($r == 1){
    		// esta rotina sofrer algumas alterações para ajustar no layout do SICREDI
    		$r_div = (int)($soma/11);
    		$digito = ($soma - ($r_div * 11));
            return $digito;
        }
    }
            
            
    function sicoob_modulo_11($num, $base=9, $r=0) {
    	$soma = 0;
    	$fator = 2; 
    	for ($i = strlen($num); $i > 0; $i--) {
    		$numeros[$i] = substr($num,$i-1,1);
    		$parcial[$i] = $numeros[$i] * $fator;
    		$soma += $parcial[$i];
    		if ($fator == $base) {
    			$fator = 1;
    		}
    		$fator++;
    	}
    	if ($r == 0) {
    		$soma *= 10;
    		$digito = $soma % 11;
    		
    		//corrigido
    		if ($digito == 10) {
    			$digito = "X";
    		}
    		
    		if (strlen($num) == "43") {
    			//então estamos checando a linha digitável
    			if ($digito == "0" or $digito == "X" or $digito > 9) {
    					$digito = 1;
    			}
    		}
    		return $digito;
    	} 
    	elseif ($r == 1){
    		$resto = $soma % 11;
    		return $resto;
    	}
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


        /*
        		
        3820	; -- PEGA ULTIMO VALOR ABATIMENTO --
        		S DTABAT=+$P($G(^CRDOCABAT(EMP,DOC,SEQ)),M,1)
        		S CRDOCABAT=$G(^CRDOCABAT(EMP,DOC,SEQ,DTABAT))
        		Q
        */ 



}
?>