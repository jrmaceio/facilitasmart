<?php
class FinRetornoBaixaTitulos extends TPage
{
    protected $form;
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');

        $this->form = new BootstrapFormBuilder('form_FinRetornoBaixaTitulos');
        $this->form->setFormTitle('Retorno Baixa Titulos');

        // cria os campos do formularios
        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
		
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', '{id} - {sigla}');
        $id_banco->enableSearch();
        
        $id_banco->setChangeAction(new TAction(array($this, 'onChangeBanco')));
        
        $criteria_cta_corrente = new TCriteria();        
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','',$criteria_cta_corrente);
        
        $id_conta_corrente->setChangeAction(new TAction(array($this, 'onChangeConta')));
        
        $criteria_nr_ret = new TCriteria();
        $criteria_nr_ret->add(new TFilter('id', '<', '0'));
        $numero_retorno = new TDBCombo('numero_retorno', 'facilitasmart', 'FinRetorno', 'id', '{numero_retorno} - {dt_retorno}','',$criteria_nr_ret);

        /*
        $criteria_movret = new TCriteria;
        $criteria_movret->add(new TFilter('id', '<', '0'));
        $id_movto_retorno = new TDBCombo('id_movto_retorno', 'facilitasmart', 'TipoMovtoRetorno', 'id', '{codigo} - {descricao}', 'codigo', $criteria_movret);
        $id_movto_retorno->enableSearch();
        */
        
        /*
        $ordem = new TRadioGroup('ordem');
        $cb = array();
        $cb['1'] = 'Confirmados&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cb['2'] = 'Não Confirmados&nbsp&nbsp&nbsp&nbsp&nbsp';
        $ordem->addItems($cb);
        $ordem->setLayout('horizontal');
        */
        
        /*
        $opcao = new TRadioGroup('opcao');
        $cb = array();
        $cb['1'] = 'Data Tela&nbsp&nbsp&nbsp&nbsp&nbsp';
        $cb['2'] = 'Data Arquivo&nbsp&nbsp&nbsp&nbsp&nbsp';
        $opcao->addItems($cb);
        $opcao->setLayout('horizontal');
        
        $dt_liquidacao = new TDate('dt_liquidacao');
        $dt_liquidacao->setMask('dd/mm/yyyy');
        $dt_liquidacao->setDatabaseMask('yyyy-mm-dd');        
        
        $dt_taxas = new TDate('dt_taxas');
        $dt_taxas->setMask('dd/mm/yyyy');
        $dt_taxas->setDatabaseMask('yyyy-mm-dd');        

        $dt_despesas = new TDate('dt_despesas');
        $dt_despesas->setMask('dd/mm/yyyy');
        $dt_despesas->setDatabaseMask('yyyy-mm-dd');        

        $dt_movto_caixa = new TDate('dt_movto_caixa');
        $dt_movto_caixa->setMask('dd/mm/yyyy');
        $dt_movto_caixa->setDatabaseMask('yyyy-mm-dd');        
        */
        
        // define os tamanhos
        $this->form->addFields( [new TLabel('Condominio')], [$id_condominio] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] , [new TLabel('Conta Corrente')], [$id_conta_corrente] );
        $this->form->addFields( [new TLabel('Retorno')], [$numero_retorno] );
        //$this->form->addFields( [new TLabel('Movto Retorno') ] , [ $id_movto_retorno ] );
        //$this->form->addFields( [new TLabel('Opção') ], [ $opcao ] );
        //$this->form->addFields( [new TLabel('Seleção') ], [ $ordem ] );
        //$this->form->addFields( [new TLabel('Dt Liquidação')], [$dt_liquidacao] , [new TLabel('Dt Movto Caixa')], [$dt_movto_caixa] );
        //$this->form->addFields( [new TLabel('Dt Taxas')], [$dt_taxas] , [new TLabel('Dt Despesas')], [$dt_despesas] );
        
        
        // size
        $id_condominio->setSize('100%');
        $id_banco->setSize('100%');
        $id_conta_corrente->setSize('100%');
        $numero_retorno->setSize('100%');
        //$id_movto_retorno->setSize('100%');
        //$dt_liquidacao->setSize('100%');
        //$dt_taxas->setSize('100%');
        //$dt_despesas->setSize('100%');
        //$dt_movto_caixa->setSize('100%');

        $panel = new TPanelGroup;
        $panel->getBody()->style = 'overflow-x:auto';

        $btn = $this->form->addAction('Executar',  new TAction([$this, 'onGenerate']), 'ico_apply.png');
        $btn->class = 'btn btn-sm btn-primary';


        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        $dt_atual = date("d/m/Y");
        
        $obj = new StdClass;        
        $obj->id_banco = 7;
        $obj->id_conta_corrente = 13;
        //$obj->dt_liquidacao  = $dt_atual;
        //$obj->dt_taxas       = $dt_atual;
        //$obj->dt_despesas    = $dt_atual;
        //$obj->dt_movto_caixa = $dt_atual;
        //$obj->ordem          = 1;
        //$obj->opcao          = 1;
        
        TForm::sendData('form_FinRetornoBaixaTitulos', $obj);

        parent::add($container);
    }
    
    
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }


    
    function onGenerate($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $data = $this->form->getData();
            $this->form->validate();
            $ordem = 1;
            $repository = new TRepository('FinRetorno');
            $criteria   = new TCriteria;
                   
            if ($ordem == 1) { $repositorysegT = new TRepository('FinRetornoSegT'); }
            if ($ordem == 2) { $repositorysegT = new TRepository('FinRetornoSegTX'); }
            $criteriasegT   = new TCriteria;
            $criteriasegT->setProperty('order','id_movto_retorno,id_movto_retorno_item,cli_nome');
            $criteriasegT->setProperty('direction','asc');
            
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
            /*
            if ($data->id_movto_retorno)
            {
                $criteriasegT->add(new TFilter('id_movto_retorno', '=', "{$data->id_movto_retorno}"));
            }
            */
            $objects = $repository->load($criteria, FALSE);
            $objectssegT = $repositorysegT->load($criteriasegT, FALSE);
            $seq = 1;   $crdsp = array();
            
            if ($objects)
            {
                foreach ($objects as $object)
                {                    
                    $id_nr_retorno    = $object->id;
                    $nr_banco         = $object->banco->sigla;
                    $nr_cta           = $object->conta_corrente->conta;
                    $nr_ret           = $object->numero_retorno;
                    $dt_ret           = $object->dt_retorno;
                    $dt_processamento = $object->dt_processamento;
                    
                    if ($dt_processamento != '') { throw new Exception('Retorno já Processado em ' . Uteis::formataData($dt_processamento,'','') . ' !!!'); }
                    
                    foreach ($objectssegT as $valueT)
                    {
                        if ($valueT->id_fin_retorno == $object->id)
                        {
                            $id_contas_receber = $valueT->id_contas_receber;
                            $reg_titulo = ContasReceber::find($id_contas_receber);  // -- pega titulo -- //
                            if ($reg_titulo)
                            {
                                $docto         = $valueT->docto;
                                $nosso_numero  = $valueT->nosso_numero;
                                $id_movto_ret  = $valueT->id_movto_retorno;
                                $reg_tipo_movto_retorno = $cod_movto_ret = $des_movto_ret = '';
                                if ($id_movto_ret > 0) {
                                    $reg_tipo_movto_retorno = TipoMovtoRetorno::find($id_movto_ret , false);
                                    $cod_movto_ret = $reg_tipo_movto_retorno->codigo;
                                    $des_movto_ret = $reg_tipo_movto_retorno->descricao;
                                }
                                $id_movto_ret_item  = $valueT->id_movto_retorno_item;
                                $reg_tipo_movto_retorno_item = $cod_movto_ret_item = $des_movto_ret_item = '';
                                if ($id_movto_ret_item > 0) {
                                    $reg_tipo_movto_retorno_item = TipoMovtoRetornoItem::find($id_movto_ret_item , false);
                                    $cod_movto_ret_item = $reg_tipo_movto_retorno_item->codigo;
                                    $des_movto_ret_item = $reg_tipo_movto_retorno_item->descricao;
                                }
                                if ($valueT->cli_pfj == 0) { $cnpj_cpf = $valueT->cli_cnpj_cpf; }
                                if ($valueT->cli_pfj == 1) { $cnpj_cpf = Uteis::formataCPF($valueT->cli_cnpj_cpf,'',''); }
                                if ($valueT->cli_pfj == 2) { $cnpj_cpf = Uteis::formataCNPJ($valueT->cli_cnpj_cpf,'',''); }
                                $forma        = $valueT->forma;
                                $dt_vcto      = $valueT->dt_vencto;
                                $vlr_titulo   = $valueT->vlr_titulo;
                                $vlr_taxa     = $valueT->vlr_taxa;
                                    
                                if ($ordem == 1) {
                                    $objectsU = FinRetornoSegU::where('id_fin_retorno','=',$object->id)
                                            ->where('id_fin_retornosegt','=',$valueT->id)
                                            ->where('id_condominio','=',$object->id_condominio)
                                            ->load();
                                }
                                if ($ordem == 2) {
                                    $objectsU = FinRetornoSegUX::where('id_fin_retorno','=',$object->id)
                                            ->where('id_fin_retornosegtx','=',$valueT->id)
                                            ->where('id_condominio','=',$object->id_condominio)
                                            ->load();
                                }
                                foreach ($objectsU as $valueU)
                                {
                                    if ($valueU->id_fin_retorno == $object->id)
                                    {
                                        $dt_baixa     = $valueU->dt_baixa;
                                        $dt_taxa      = $valueU->dt_taxa;
                                        $dt_credito   = $valueU->dt_credito;
                                        $vlr_juros    = $valueU->vlr_juros;
                                        $vlr_descto   = $valueU->vlr_descto;
                                        $vlr_abatim   = $valueU->vlr_abatimento;
                                        $vlr_pago     = $valueU->vlr_pago;
                                        $vlr_credito  = $valueU->vlr_credito;
                                        $vlr_out_desp = $valueU->vlr_out_desp;
                                        $vlr_out_cred = $valueU->vlr_out_credito;
                                        $vlr_devol    = 0;
                                        /*
                                        print "<br>";
                                        print "<br>  retorno ----------> " . $id_nr_retorno . "-". $nr_ret . "-" . $dt_ret;
                                        print "<br>  cod mov ret ------> " . $id_movto_ret . "-" . $cod_movto_ret . "-" . $des_movto_ret; 
                                        print "<br>  cod mov ret item -> " . $id_movto_ret_item . "-" . $cod_movto_ret_item . "-" . $des_movto_ret_item;
                                        print "<br>  id_contas_receber-> " . $id_contas_receber . "  docto-> " . $docto . "  nosso_numero-> " . $nosso_numero;
                                        */
                                        $vlr_sld_titulo = 0; if ($id_contas_receber > 0) { $vlr_sld_titulo = $this->onBuscaTitulo($id_contas_receber); }
                                        // -- entrada confirmada
                                        if ($cod_movto_ret == '02') {
                                            if ($reg_titulo->boleto_status != 2) {
                                                $reg_retorno = $this->onConfirmaTitulo( $id_contas_receber , $nr_ret , $id_nr_retorno , $object->id_conta_corrente );
                                    			//print "<br> mov 02 Confirma Registro" . " - id titulo -> " . $id_contas_receber . " - vlr titulo -> " . $vlr_sld_titulo;
                                            }
                                        }
                                        // -- entrada rejeitada
                                		if ($cod_movto_ret == '03') {
                                			//print "<br> mov 03 entrada rejeitada" . " - id titulo -> " . $id_contas_receber . " - vlr titulo -> " . $vlr_sld_titulo;
                                		}
                                		// -- liquidacao
                                		if ($cod_movto_ret == '06') {
                                		    if ($reg_titulo->situacao == 0) {
                                    		    $reg_retorno = $vlr_liquida = 0;  
                                    		    if ($vlr_sld_titulo > 0) {
                                                    $vlr_liquida = $vlr_sld_titulo + $vlr_juros - $vlr_descto - $vlr_devol;  
                                                    $reg_retorno = $this->onGeraLiquidacao( $id_contas_receber , $nr_ret , $id_nr_retorno , $object->id_conta_corrente , $dt_credito , $dt_baixa , $dt_taxa , $vlr_sld_titulo , $vlr_juros , $vlr_descto , $vlr_devol , $vlr_liquida );
                                                    //print "<br> mov 06 liquidacao" . " - id titulo -> " . $id_contas_receber . " - vlr titulo -> " . $vlr_sld_titulo . " - vlr liquidacao -> " . $vlr_liquida . " - situacao -> " . $reg_retorno;
                                                }
                                			}
                            		    }
                                		// -- CONFIRMACAO DO RECEBIMENTO DA INSTRUCAO DE DESCONTO
                                		if ($cod_movto_ret == '07') {
                                		    //print "<br> mov 07 CONFIRMACAO DO RECEBIMENTO DA INSTRUCAO DE DESCONTO";
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO DO CANCELAMENTO DO DESCONTO
                                		if ($cod_movto_ret == '08') {
                                		    //print "<br> mov 08 CONFIRMACAO DO RECEBIMENTO DO CANCELAMENTO DO DESCONTO";
                                		}
                                		// -- baixa
                                		if ($cod_movto_ret == '09') {
                                			//print "<br> mov 09 baixa" . " - id titulo -> " . $id_contas_receber . " - vlr titulo -> " . $vlr_sld_titulo;
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE ABATIMENTO
                                		if ($cod_movto_ret == '12') {
                                			//print "<br> mov 12 CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE ABATIMENTO";
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE CANCELAMENTO ABATIMENTO
                                		if ($cod_movto_ret == '13') {
                                		    //print "<br> mov 13 CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE CANCELAMENTO ABATIMENTO";
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO INSTRUCAO ALTERACAO DE VENCIMENTO
                                		if ($cod_movto_ret == '14') {
                                		    //print "<br> mov 14 CONFIRMACAO DO RECEBIMENTO INSTRUCAO ALTERACAO DE VENCIMENTO";
                                		}
                                		// -- LIQUIDACAO APOS BAIXA OU LIQUIDACAO TITULO NAO REGISTRADO
                                		if ($cod_movto_ret == '17') {
                                		    //print "<br> mov 17 LIQUIDACAO APOS BAIXA OU LIQUIDACAO TITULO NAO REGISTRADO";
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE PROTESTO
                                		if ($cod_movto_ret == '19') {
                                		    //print "<br> mov 19 CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE PROTESTO";
                                		}
                                		// -- CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE SUSTACAO/CANCELAMENTO DE PROTESTO
                                		if ($cod_movto_ret == '20') {
                                		    //print "<br> mov 20 CONFIRMACAO DO RECEBIMENTO INSTRUCAO DE SUSTACAO/CANCELAMENTO DE PROTESTO";
                                		}
                                		// -- REMESSA A CARTORIO (APONTE EM CARTORIO
                                		if ($cod_movto_ret == '23') {
                                			//print "<br> mov 23 REMESSA A CARTORIO (APONTE EM CARTORIO";
                                		}
                                		// -- RETIRADA DE CARTORIO E MANUTENCAO EM CARTEIRA
                                		if ($cod_movto_ret == '24') {
                                			//print "<br> mov 24 RETIRADA DE CARTORIO E MANUTENCAO EM CARTEIRA";
                                		}
                                		// -- PROTESTADO E BAIXADO (BAIXA POR TER SIDO PROTESTADO
                                		if ($cod_movto_ret == '25') {
                                			//print "<br> mov 25 PROTESTADO E BAIXADO (BAIXA POR TER SIDO PROTESTADO";
                                		}
                                		// -- INSTRUCAO REJEITADA
                                		if ($cod_movto_ret == '26') {
                                			//print "<br> mov 26 INSTRUCAO REJEITADA";
                                		}
                                		// -- CONFIRMACAO DO PEDIDO DE ALTERACAO DE OUTROS DADOS
                                		if ($cod_movto_ret == '27') {
                                		    //print "<br> mov 27 CONFIRMACAO DO PEDIDO DE ALTERACAO DE OUTROS DADOS";
                                		}
                                		// -- DEBITO DE TARIFAS CUSTAS
                                		if ($cod_movto_ret == '28') {
                                		    if ( ($reg_titulo->tarifa == '') || ($reg_titulo->tarifa == 0) ) {
                                    		    if ($vlr_taxa > 0) {
                                                    $reg_retorno = $this->onGeraDespesa( $id_contas_receber , $nr_ret , $id_nr_retorno , $object->id_conta_corrente , $dt_credito , $vlr_taxa );
                                                    //print "<br> mov 28 DEBITO DE TARIFAS CUSTAS" . " - id titulo -> " . $id_contas_receber . " - vlr titulo -> " . $vlr_sld_titulo . " - vlr taxa -> " . $vlr_taxa;
                                                }
                                            }
                                		}
                                		// -- ALTERACAO DE DADOS REJEITADA
                                		if ($cod_movto_ret == '30') {
                                		    //print "<br> mov 30 ALTERACAO DE DADOS REJEITADA";
                                		}
                                		// -- BAIXA REJEITADA
                                		if ($cod_movto_ret == '36') {
                                		    //print "<br> mov 36 BAIXA REJEITADA";
                                		}
                                		// -- TITULO DDA RECONHECIDO PELO PAGADOR
                                		if ($cod_movto_ret == '51') {
                                		    //print "<br> mov 51 TITULO DDA RECONHECIDO PELO PAGADOR";
                                		}
                                		// -- TITULO DDA NAO RECONHECIDO PELO PAGADOR
                                		if ($cod_movto_ret == '52') {
                                		    //print "<br> mov 52 TITULO DDA NAO RECONHECIDO PELO PAGADOR";
                                		}
                                		// -- CONFIRMACAO DE RECEBIMENTO DE PEDIDO DE NEGATIVACAO
                                		if ($cod_movto_ret == '78') {
                                		    //print "<br> mov 78 CONFIRMACAO DE RECEBIMENTO DE PEDIDO DE NEGATIVACAO";
                                		}
                                		// -- CONFIRMACAO DE RECEBIMENTO DE PEDIDO DE EXCLUSAO DE NEGATIVACAO
                                		if ($cod_movto_ret == '79') {
                                		    //print "<br> mov 79 CONFIRMACAO DE RECEBIMENTO DE PEDIDO DE EXCLUSAO DE NEGATIVACAO";
                                		}
                                		// -- CONFIRMACAO DE ENTRADA DE NEGATIVACAO
                                		if ($cod_movto_ret == '80') {
                                		    //print "<br> mov 80 CONFIRMACAO DE ENTRADA DE NEGATIVACAO";
                                		}
                                		// -- ENTRADA DE NEGATIVACAO REJEITADA
                                		if ($cod_movto_ret == '81') {
                                		    //print "<br> mov 81 ENTRADA DE NEGATIVACAO REJEITADA";
                                		}
                                		// -- CONFIRMACAO DE EXCLUSAO DE NEGATIVACAO
                                		if ($cod_movto_ret == '82') {
                                		    //print "<br> mov 82 CONFIRMACAO DE EXCLUSAO DE NEGATIVACAO";
                                		}
                                		// -- EXCLUSAO DE NEGATIVACAO REJEITADA
                                		if ($cod_movto_ret == '83') {
                                		    //print "<br> mov 83 EXCLUSAO DE NEGATIVACAO REJEITADA";
                                		}
                                		// -- EXCLUSAO DE NEGATIVACAO POR OUTROS MOTIVOS
                                		if ($cod_movto_ret == '84') {
                                		    //print "<br> mov 84 EXCLUSAO DE NEGATIVACAO POR OUTROS MOTIVOS";
                                		}
                                		// -- OCORRENCIA INFORMACIONAL POR OUTROS MOTIVOS
                                		if ($cod_movto_ret == '85') {
                                		    //print "<br> mov 85 OCORRENCIA INFORMACIONAL POR OUTROS MOTIVOS";
                                        }
                                		
                                    } // fim  if ($valueU->id_fin_retorno == $object->id)
                                    
                                } // fim foreach ($objectsU as $valueU)
    
                            } // fim  if ($reg_titulo)
                        
                        } // fim  if ($valueT->id_fin_retorno == $object->id)
                        
                    } // fim foreach ($objectsT as $valueT)
                    
                } // fim foreach ($objects as $object)
            
            
                $object->dt_processamento = date("Y-m-d");
                $object->store();
                
                new TMessage('info', 'Retorno Processado com Sucesso !!!');
                
            } // fim  if ($objects)
            
            else { new TMessage('error', 'Nao existe dados para serem salvos !!!'); }
    
            $this->form->setData($data);
            TTransaction::close();

        }  // fim try
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    } // fim onGenerate



    public static function onChangeBanco($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            if (!empty($param['id_banco']))
            {    
                $criteria = TCriteria::create( ['id_banco' => $param['id_banco'] ] );
                TDBCombo::reloadFromModel('form_FinRetornoBaixaTitulos', 'id_movto_retorno', 'facilitasmart', 'TipoMovtoRetorno', 'id', '({codigo}) {descricao}', 'codigo', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_FinRetornoBaixaTitulos', 'id_movto_retorno');
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
                TDBCombo::reloadFromModel('form_FinRetornoBaixaTitulos', 'numero_retorno', 'facilitasmart', 'FinRetorno', 'id', '{numero_retorno}', 'id', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_FinRetornoBaixaTitulos', 'numero_retorno');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }



    public static function onBuscaTitulo($param)
    {
        $idtit = $param;
        $reg_contas_receber = new ContasReceber($idtit,FALSE);
        $vlr_saldo    = $vlr_titulo   = $reg_contas_receber->valor;
        $situacao     = $reg_contas_receber->situacao;
        if ($reg_contas_receber->situacao != 0) { $vlr_saldo = 0; }  // -- valida saldo titulo -- //
        return $vlr_saldo;
    } 


    public static function onConfirmaTitulo( $id_contas_receber , $nr_ret , $id_nr_retorno , $cta_corrente )
    {
        $reg_contas_receber = ContasReceber::find($id_contas_receber);  // -- pega titulo -- //
        $reg_contas_receber->boleto_status = 5; // titulo registrado
        //print "<br> gravou Confirma";
        $reg_contas_receber->store();
    } // fim  onConfirmaTitulo



    public static function onGeraLiquidacao( $id_contas_receber , $nr_ret , $id_nr_retorno , $cta_corrente , $dt_credito , $dt_baixa , $dt_taxa , $vlr_saldo , $vlr_juros , $vlr_descto , $vlr_devol , $vlr_liquida )
    {
        $reg_contas_receber = ContasReceber::find($id_contas_receber);  // -- pega titulo -- //
        $reg_contas_receber->dt_pagamento    = $dt_baixa;
        $reg_contas_receber->dt_liquidacao   = $dt_credito;
        $reg_contas_receber->valor_pago      = $vlr_liquida;
        $reg_contas_receber->desconto        = $vlr_descto;
        $reg_contas_receber->juros           = $vlr_juros;
        //$reg_contas_receber->multa           = 0;
        //$reg_contas_receber->tarifa          = 0;
        $reg_contas_receber->valor_creditado = $vlr_liquida;
        $reg_contas_receber->situacao        = 1;  // -- liquidado -- //  
        //print "<br> gravou Liquidacao";
        $reg_contas_receber->store();
        return $reg_contas_receber->situacao;
    } // fim onGeraLiquidacao



    public static function onGeraDespesa( $id_contas_receber , $nr_ret , $id_nr_retorno , $cta_corrente , $dt_credito , $vlr_taxa )
    {
        $reg_contas_receber = ContasReceber::find($id_contas_receber);  // -- pega titulo -- //
        $reg_contas_receber->tarifa          = $vlr_taxa;  
        //print "<br> gravou Taxa";
        $reg_contas_receber->store();
    } // fim  onGeraDespesa

  
   
}
?>