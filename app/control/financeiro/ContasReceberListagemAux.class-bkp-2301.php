<?php
class ContasReceberListagemAux extends TWindow
{

    static function onInputDialog( $param )
    {
        $id = $param['id'];

            //print "<br>";
            //print_r ($param);
        
        TTransaction::open('facilitasmart');
        $reg_titulo = ContasReceber::find($id,false);
        $reg_cta = ContaCorrente::find( $reg_titulo->id_conta_corrente );
        TTransaction::rollback();
        
        $quick = new TQuickForm('input_form');
        $quick->style = 'padding:20px';
         
        $rotina = new THidden('rotina');
        $rotina->setValue($param['class']);
        
        $titulo = new TEntry('titulo');
        $titulo->setValue($id);
        $titulo->setEditable(FALSE);
        
        $conta_corrente = new TDBCombo('conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','id');
        if ($reg_titulo->id_conta_corrente != '') { $conta_corrente->setValue($reg_titulo->id_conta_corrente); }
        
        $banco_carteira = new TDBCombo('banco_carteira', 'facilitasmart', 'Banco', 'id', 'sigla');
        $banco_carteira->setEditable(FALSE);
        if ($reg_titulo->id_conta_corrente != '') { $banco_carteira->setValue($reg_cta->id_banco); }
        
        //$nosso_numero = new TEntry('nosso_numero');
        //$nosso_numero->setEditable(FALSE);
        
        $modelo = new TRadioGroup('modelo');
        $modelo->addItems(array('1'=>'Clássico', '2'=>'Carnê', '3'=>'Informativo'));
        $modelo->setLayout('horizontal');
        $modelo->setValue(1);
        
        $conta_corrente->setSize('70%');
        $banco_carteira->setSize('70%');
        $modelo->setSize('100%');
        //$nosso_numero->setSize('100%');
        
        $quick->addQuickField('Titulo', $titulo);
        $quick->addQuickField('', $rotina);
        $quick->addQuickField('Conta Corrente', $conta_corrente);
        $quick->addQuickField('Banco/Carteira', $banco_carteira);
        $quick->addQuickField('Modelo', $modelo);
        //$quick->addQuickField('Nosso Numero', $nosso_numero);
        $conta_corrente->setChangeAction(new TAction(array('ContasReceberListagemAux', 'onExitCtDialog')));
        $quick->addQuickAction('Avançar', new TAction(array('ContasReceberListagemAux', 'onBoleto')), 'fa:arrow-circle-right green');
        // show the input dialog
        new TInputDialog('Insira os Dados', $quick);
    }


    static function onExitCtDialog( $param )
    {
        TTransaction::open('facilitasmart');
        $banco = ContaCorrente::find($param['conta_corrente'])->id_banco;
        $obj = new StdClass;
        $obj->banco_carteira = $banco;
        TTransaction::rollback();
        TForm::sendData('input_form', $obj);
    }


    static function onBoleto($param)
    {
        try {
            TTransaction::open('facilitasmart');
            
            if (!isset($param['conta_corrente']) OR empty($param['conta_corrente']) ) {
                $pos_action = new TAction(['ContasReceberListagem', 'onInputDialog']);
                $pos_action->setParameter('id',$param['titulo']);
                new TMessage('warning','Selecione a Conta!!!', $pos_action);
            } else {
                $id_titulo      = $param['titulo'];
                $reg_fin_titulo = ContasReceber::find($id_titulo);
                
                $condominio = new Condominio($reg_fin_titulo->condominio_id);
                $classe = new PlanoContas($reg_fin_titulo->classe_id);
                $unidade = new Unidade($reg_fin_titulo->unidade_id);
                $id_favorecido = $unidade->proprietario_id;
                
                $reg_favorecido = new Pessoa($unidade->proprietario_id);
				
                $reg_empresa = $condominio; // nesta tabela existem os dados da empresa/condomínio (endereco, etc.)
                
                $endereco1 = $reg_favorecido->endereco . " nr. " . $reg_favorecido->numero;
                $endereco2 = $reg_favorecido->bairro . " - " . $reg_favorecido->cidade . " - " . $reg_favorecido->estado . " - " . 
                             $reg_favorecido->cep;

                $demonstrativo = 'Referência :' . 'Mês Ref.: ' . $reg_fin_titulo->mes_ref . ' Descrição: ' . $reg_fin_titulo->descricao;
                
                $texto = $demonstrativo . '<br>SR CAIXA NÃO RECEBER APOS O VENCIMENTO';  $textom = $textoj = $textod = '';
                if ( ($reg_fin_titulo->multa_boleto_cobranca > 0) || ($reg_fin_titulo->multa_boleto_cobranca != '') ) 
                { 
                    //$vlr_multa = round ( ( ( $reg_fin_titulo->valor * $reg_fin_titulo->multa_boleto_cobranca ) / 100 ) , 2 );
                    //$textom = ' Multa de R$ ' . Uteis::numeroBrasil($vlr_multa) . ' - ' . Uteis::numeroBrasil($reg_fin_titulo->multa_boleto_cobranca) . ' % ao mes.'; 
                    $textom = ' Multa de ' . Uteis::numeroBrasil($reg_fin_titulo->multa_boleto_cobranca) . ' % ao mes.';
                } 
                if ( ($reg_fin_titulo->juros_boleto_cobranca > 0) || ($reg_fin_titulo->juros_boleto_cobranca != '') ) 
                { 
                    //$txa_juros = round ( ( $reg_fin_titulo->juros_boleto_cobranca / 30 ) , 4 ); 
                    //$vlr_juros = round ( ( ( $reg_fin_titulo->valor * $txa_juros ) / 100 ) , 2 );
                    //$textoj = ' Juros de R$ ' . Uteis::numeroBrasil($vlr_juros) . ' - ' . Uteis::numeroBrasil($txa_juros , 4) . ' % ao dia.'; 
                    $textoj = ' Juros de ' . Uteis::numeroBrasil($reg_fin_titulo->juros_boleto_cobranca) . ' % ao mes.';
                }
                if ( ( $textom != '') || ( $textoj != '' ) ) { $texto = $texto . '<br>Após vencimento: ' . $textom . $textoj; }
                if ( ($reg_fin_titulo->desconto_boleto_cobranca > 0) || ($reg_fin_titulo->desconto_boleto_cobranca != '') ) { $textod = '<br>Ate dia ' . Uteis::formataData($reg_fin_titulo->dt_limite_desconto_boleto_cobranca ,'','') . ' conceder desconto de R$ ' . Uteis::numeroBrasil($reg_fin_titulo->desconto_boleto_cobranca) . ', cobrar R$ ' . Uteis::numeroBrasil( ( $reg_fin_titulo->valor - $reg_fin_titulo->desconto_boleto_cobranca ) ) . '.'; }
                if ( $textod != '') { $texto = $texto . $textod; }
                $instrucoes = $texto; 
                                    
                $nossonum = $reg_fin_titulo->nosso_numero; // busca nosso numero já atribuido (caso ja exista)
                
                $boleto = array();
                $boleto['rotina']             = $param['rotina'];
                $boleto['id_titulo']          = $id_titulo;
                
                $boleto['numero_documento']   = $reg_fin_titulo->id;

                if ($reg_fin_titulo->documento_boleto_cobranca != '') {
                    $boleto['numero_documento'] = $reg_fin_titulo->documento_boleto_cobranca;
                }
                
                $boleto['data_documento']     = Uteis::formataData($reg_fin_titulo->dt_lancamento,'','');
                $boleto['data_vencimento']    = Uteis::formataData($reg_fin_titulo->dt_vencimento,'','');
                $boleto['valor_boleto']       = Uteis::numeroBrasil($reg_fin_titulo->valor);
                $boleto['sacado']             = Uteis::numeroEsquerda($reg_fin_titulo->unidade_id,6) . " - " . $reg_favorecido->nome . " - " . $unidade->bloco_quadra . "-" . $unidade->descricao;
                $boleto['endereco1']          = $endereco1;
                $boleto['endereco2']          = $endereco2;
                $boleto['demonstrativo']      = $demonstrativo;
                $boleto['instrucoes']         = $instrucoes;
                $boleto['flag_sistema']       = 'S';
                $boleto['agencia']            = ContaCorrente::find($param['conta_corrente'])->agencia;
                $explode = explode("-", ContaCorrente::find($param['conta_corrente'])->conta );
                $boleto['conta']     = $explode[0];
                $boleto["conta_dv"]  = $explode[1];
                $boleto['id_conta']           = $param['conta_corrente'];
                $boleto['convenio']           = ContaCorrente::find($param['conta_corrente'])->convenio;
                $boleto['codigo_banco']       = Banco::find($param['banco_carteira'])->codigo_bacen;
                $boleto['id_banco']           = $param['banco_carteira'];
                $boleto['modelo']             = $param['modelo'];
                
                if ($param['banco_carteira'] == 7) { // sicred
                    $boleto["carteira"]             = '01';// alterado junior 20-10-2020 07:36//20;  // ano
                    $boleto["posto"]                = ContaCorrente::find($param['conta_corrente'])->posto; // 4;     //$boleto['posto']        = 4;
                    $boleto["byte_idt"]             = 2;     //$boleto['indicador']    = 2;  
                    $boleto["inicio_nosso_numero"]  = 20;    // $boleto['ano']          = 21;                          
                }
                
                if ($param['banco_carteira'] == 8) {
                    $boleto["carteira"]             = 1;     
                    $boleto["inicio_nosso_numero"]  = 20;        
                    $boleto["modalidade_cobranca"]  = '01';

                    $boleto["numero_parcela"]       = '01';
                }
                
                if ( (ContaCorrente::find( $param['conta_corrente'] )->tipo_inscricao) == 'F') { 
                    $boleto["cpf_cnpj"] = ContaCorrente::find( $param['conta_corrente'] )->inscricao_cpf; 
                }
                
                if ( (ContaCorrente::find( $param['conta_corrente'] )->tipo_inscricao) == 'J') { 
                    $boleto["cpf_cnpj"] = ContaCorrente::find( $param['conta_corrente'] )->inscricao_cnpj; 
                }
                
                $boleto["identificacao"] = $reg_empresa->nome; // "Sacador";
                $boleto["endereco"]   = $reg_empresa->endereco . " - " . $reg_empresa->numero . " - " . $reg_empresa->bairro . " - " . $reg_empresa->cep;
                $boleto["cidade_uf"]  = $reg_empresa->cidade . " - " . $reg_empresa->estado; 
                $boleto["cedente"]    = ContaCorrente::find( $param['conta_corrente'] )->titular;
                $boleto["quantidade"] = "";
                $boleto["valor_unitario"] = "";
                $boleto["aceite"] = "N";        
                $boleto["especie"] = "R$";
                $boleto["especie_doc"] = "DMI";
                
                // -- se nossonum for vazio (caso nao tenha) vai pegar sequencial novo
                if ($nossonum == '') {
                    $reg_cta_nossonr_gravado = ContaCorrenteNossoNumero::where('id_condominio', '=', $reg_fin_titulo->condominio_id)
                                                            ->where('id_conta_corrente', '=', $param['conta_corrente'])
                                                            ->load();
                                                            
                    $reg_cta_nossonr_gravado_reverse = array_reverse($reg_cta_nossonr_gravado); //inverte tabela 
                    $nseq = $ct = 0;
                    foreach ($reg_cta_nossonr_gravado_reverse as $value) {
                       if ($ct == 0) { $nseq = $value->sequencial + 1; }
                       $ct = $ct + 1;
                    } // fim foreach ($reg_cta_nossonr_gravado_reverse as $value)
                    if ($nseq == 0) { $nseq = 1; }
                    $reg_cta_nossonr = new ContaCorrenteNossoNumero;
                    $reg_cta_nossonr->id_condominio     = $reg_fin_titulo->condominio_id;
                    $reg_cta_nossonr->id_conta_corrente = $param['conta_corrente'];
                    $reg_cta_nossonr->sequencial        = $nseq;
                    $reg_cta_nossonr->id_contas_receber = $id_titulo;
                    $reg_cta_nossonr->store();
                    $boleto['nseq']                     = $nseq;
                    $boleto['nosso_numero']             = '';
                } // fim if ($nossonum == '')
                
                if ($nossonum != '') {
                    $boleto['nseq']             = (int)substr($nossonum,4,5);
                    $boleto['nosso_numero']     = $nossonum;
                }

                TApplication::loadPage('FinBoletoView', 'onGenerate', (array) $boleto);
            } // fim else   
            TTransaction::close();
        } // fim try
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            //TApplication::postData('input_form','ContasReceberListagem');
        }
    }



}
