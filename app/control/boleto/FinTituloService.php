<?php
class FinTituloService
{

    // 1-idclierp // 2-idempresa // 3-idtitulo // 4-dt movto // 5-idtipoliquidacao 
    // 6-valor movto  // 7-id_titulo_liquida  // 8-id_transacao_liquidacao
    public static function setTituloMovto($idclierp , $idemp , $idfintitulo , $dtmov , $idtpliq , $vlrmov , $idtitliq , $idtransliq)
    {
        // busca dados do titulo //
        $reg_fintit = new FinTitulo($idfintitulo); // registro fintitulo
        // novo registro //        
        $reg_fintitulomovto = new FinTituloMovto;
        $reg_fintitulomovto->id_cliente_erp          = $idclierp;
        $reg_fintitulomovto->id_empresa              = $idemp;
        $reg_fintitulomovto->id_fin_titulo           = $idfintitulo;
        $reg_fintitulomovto->dt_movto                = $dtmov;
        $reg_fintitulomovto->id_tipo_liquidacao      = $idtpliq;
        $reg_fintitulomovto->vlr_movto               = $vlrmov;
        $reg_fintitulomovto->id_fin_recibo_liquida   = 0;
        $reg_fintitulomovto->id_fin_titulo_liquida   = $idtitliq;
        $reg_fintitulomovto->id_transacao_liquidacao = $idtransliq;
        $reg_fintitulomovto->store(); // save master object
        $reg_volta = $reg_fintitulomovto->id;
        return $reg_volta;
   }


    // 1-idclierp // 2-idempresa // 3-idtitulo 
    // 4-idtitulomovtobxa // 5-idtitulomovtojrs // 6-idtitulomovtodes // 7-idtitulomovtodev
    public static function setTituloMovtoIDs($idclierp , $idemp , $idfintitulo , $idtitmovbxa , $idtitmovjrs , $idtitmovdes , $idtitmovdev )
    {
        $reg_volta = 0;
        if ( ($idtitmovbxa != '') || ($idtitmovbxa != 0) )
        {         
            $reg_fintitulomovto = FinTituloMovto::find($idtitmovbxa,false);
            $reg_fintitulomovto->id_fin_titulo_movto_bxa = $idtitmovbxa;
            $reg_fintitulomovto->id_fin_titulo_movto_jrs = $idtitmovjrs;
            $reg_fintitulomovto->id_fin_titulo_movto_des = $idtitmovdes;
            $reg_fintitulomovto->id_fin_titulo_movto_dev = $idtitmovdev;
            $reg_fintitulomovto->store(); // save master object
            $reg_volta = 2;
        }
        if ( ($idtitmovjrs != '') || ($idtitmovjrs != 0) )
        {         
            $reg_fintitulomovto = FinTituloMovto::find($idtitmovjrs,false);
            $reg_fintitulomovto->id_fin_titulo_movto_bxa = $idtitmovbxa;
            $reg_fintitulomovto->id_fin_titulo_movto_jrs = $idtitmovjrs;
            $reg_fintitulomovto->id_fin_titulo_movto_des = $idtitmovdes;
            $reg_fintitulomovto->id_fin_titulo_movto_dev = $idtitmovdev;
            $reg_fintitulomovto->store(); // save master object
            $reg_volta = 3;
        }
        if ( ($idtitmovdes != '') || ($idtitmovdes != 0) )
        {         
            $reg_fintitulomovto = FinTituloMovto::find($idtitmovdes,false);
            $reg_fintitulomovto->id_fin_titulo_movto_bxa = $idtitmovbxa;
            $reg_fintitulomovto->id_fin_titulo_movto_jrs = $idtitmovjrs;
            $reg_fintitulomovto->id_fin_titulo_movto_des = $idtitmovdes;
            $reg_fintitulomovto->id_fin_titulo_movto_dev = $idtitmovdev;
            $reg_fintitulomovto->store(); // save master object
            $reg_volta = 4;
        }
        if ( ($idtitmovdev != '') || ($idtitmovdev != 0) )
        {         
            $reg_fintitulomovto = FinTituloMovto::find($idtitmovdev,false);
            $reg_fintitulomovto->id_fin_titulo_movto_bxa = $idtitmovbxa;
            $reg_fintitulomovto->id_fin_titulo_movto_jrs = $idtitmovjrs;
            $reg_fintitulomovto->id_fin_titulo_movto_des = $idtitmovdes;
            $reg_fintitulomovto->id_fin_titulo_movto_dev = $idtitmovdev;
            $reg_fintitulomovto->store(); // save master object
            $reg_volta = 5;
        }
        return $reg_volta;
   }



    // 1-idclierp // 2-idempresa // 3-idtitulo // 4-dt movto // 5-idtipoliquidacao // 6-id titulo movto   
    public static function excTituloMovto($idclierp , $idemp , $idfintitulo , $dtmov , $idtpliq , $idtitmov)
    {
        $reg_fintit = new FinTitulo($idfintitulo); // registro fintitulo
        // exclui registro //        
        FinTituloMovto::where('id_cliente_erp','=',$idclierp)
                    ->where('id_empresa','=',$idemp)
                    ->where('id_fin_titulo','=',$idfintitulo)
                    ->where('dt_movto','=',$dtmov)
                    ->where('id_tipo_liquidacao','=',$idtpliq)
                    ->where('id','=',$idtitmov)
                    ->delete();
        $reg_volta = 1;
        return $reg_volta;
   }

    // 1-idclierp // 2-idempresa // 3-dt movto // 4-cc // 5-valor movto // 6-plano contas  
    // 7-ccusto // 8-dc // 9-id_titulo_liquida  // 10-id_titulo  // 11-favorecido  
    // 12-tipo titulo origem  // 13-origem 1-fin x mov  2-mov x fin  // 14-docto 
    public static function setMovtoCaixa($idclierp , $idemp , $dtmov , $cc , $vlrmov , $idplano , $idcusto , $dc , $idfinliq , $idtit , $idfav , $idtpdocorig , $origem , $docto )
    {        
        $reg_finmovtocaixa = new FinMovtoCaixa;
        $reg_finmovtocaixa->id_cliente_erp         = $idclierp;
        $reg_finmovtocaixa->id_empresa             = $idemp;
        $reg_finmovtocaixa->dt_movto               = $dtmov;
        $reg_finmovtocaixa->id_conta_corrente      = $cc;
        $reg_finmovtocaixa->valor_lancto           = $vlrmov;
        $reg_finmovtocaixa->id_fin_recibo          = 0;
        $reg_finmovtocaixa->id_fin_recibo_liquida  = 0;
        $reg_finmovtocaixa->id_fin_recibo_troco    = 0;  
        $reg_finmovtocaixa->id_fin_titulo_liquida  = $idfinliq;
        $reg_finmovtocaixa->id_fin_titulo          = $idtit;
        
        $reg_finmovtocaixa->id_favorecido          = $idfav;
        $reg_finmovtocaixa->id_tipo_titulo_origem  = $idtpdocorig;
        $reg_finmovtocaixa->origem                 = $origem;
        
        $reg_finmovtocaixa->id_plano_contas        = $idplano;
        $reg_finmovtocaixa->id_centro_custo        = $idcusto;
        $reg_finmovtocaixa->dc                     = $dc;
        
        $reg_favorecido = Pessoa::find($idfav);
        if ($idtpdocorig == 1) { $hist_ini = 'RECBTO TITULO NR. '; }
        if ($idtpdocorig == 2) { $hist_ini = 'PAGTO TITULO NR. '; }
        if ($idtpdocorig == 3) { $hist_ini = 'RECBTO CHEQUE NR. '; }
        if ($idtpdocorig == 4) { $hist_ini = 'PAGTO CHEQUE NR. '; }
        if ($idtpdocorig == 5) { $hist_ini = 'RECBTO PROMISSORIA NR. '; }
        if ($idtpdocorig == 6) { $hist_ini = 'PAGTO PROMISSORIA NR. '; }
        if ($idtpdocorig == 7) { $hist_ini = 'RECBTO NOTA DEBITO NR. '; }
        if ($idtpdocorig == 8) { $hist_ini = 'PAGTO NOTA DEBITO NR. '; }
        $hist = $hist_ini . Uteis::numeroEsquerda($idtit,6) . ' REF.DOCTO ' . $docto . ' - ' . $reg_favorecido->nome;
        
        $reg_finmovtocaixa->historico              = $hist;
        $reg_finmovtocaixa->docto                  = $docto;
        
        $reg_finmovtocaixa->store(); // save master object
        $reg_volta = $reg_finmovtocaixa->id;
        return $reg_volta;
   }


    // 1-idclierp // 2-idempresa // 3-dt movto // 4-cc // 5-id_titulo  // 6-id fin movto caixa
    public static function excMovtoCaixa($idclierp , $idemp , $dtmov , $cc , $idtit , $idmovcxa)
    {
        FinMovtoCaixa::where('id_cliente_erp','=',$idclierp)
                    ->where('id_empresa','=',$idemp)
                    ->where('id_conta_corrente','=',$cc)
                    ->where('dt_movto','=',$dtmov)
                    ->where('id_fin_titulo','=',$idtit)
                    ->where('id','=',$idmovcxa)
                    ->delete();
        $reg_volta = 1;
        return $reg_volta;
   }


    //1-id_fin_titulo //2-nosso numero //3-id_carteira (banco) //4-id_conta_corrente
    public static function geraNossoNumero($idtitulo, $nossonumero, $id_banco, $id_conta)
    {
        TTransaction::open('facilitasmart');
        $reg_fin_titulo = ContasReceber::find($idtitulo);
        //$reg_fin_titulo->id_carteira = $id_banco;  *//defuso//
        $reg_fin_titulo->id_conta_corrente = $id_conta;
        $reg_fin_titulo->nosso_numero = $nossonumero;
        $reg_fin_titulo->store();
        TTransaction::close();
    }
    
    
    
    // 1-idclierp // 2-idempresa // 3-id fin movto caixa  // 4-id fin titulo liquida  // 5-id fin titulo movto
    public static function setMovCxaLiq($idclierp , $idemp , $idmovcxa , $idtitliq , $idtitmov )
    {        
        $reg_movcx_liq = new FinMovtoCaixaLiquida;
        $reg_movcx_liq->id_cliente_erp        = $idclierp;
        $reg_movcx_liq->id_empresa            = $idemp;
        $reg_movcx_liq->id_fin_movto_caixa    = $idmovcxa;
        $reg_movcx_liq->id_fin_titulo_liquida = $idtitliq;
        $reg_movcx_liq->id_fin_titulo_movto   = $idtitmov;
        $reg_movcx_liq->store();
        $reg_volta = $reg_movcx_liq->id;
        return $reg_volta;
   }
   
    // 1-idclierp // 2-idempresa // 3-id fin movto caixa  // 4-id fin titulo liquida // 5-id fin titulo movto  
    public static function excMovCxaLiq($idclierp , $idemp , $idmovcxa , $idtitliq , $idtitmov )
    {
        $xx_movcxa_liq = FinMovtoCaixaLiquida::where('id_cliente_erp', '=', $idclierp)
                                            ->where('id_empresa', '=', $idemp)
                                            ->where('id_fin_movto_caixa','=', $idmovcxa)
                                            ->where('id_fin_titulo_liquida','=', $idtitliq)
                                            ->where('id_fin_titulo_movto','=', $idtitmov)
                                            ->delete();
        $reg_volta = 1;
        return $reg_volta;
    }

    // 1-idclierp // 2-idempresa // 3-id fin titulo movto  // 4-id fin titulo liquida  
    public static function setTitMovLiq($idclierp , $idemp , $idtitmov , $idtitliq )
    {        
        $reg_tit_mov_liq = new FinTituloMovtoLiquida;
        $reg_tit_mov_liq->id_cliente_erp        = $idclierp;
        $reg_tit_mov_liq->id_empresa            = $idemp;
        $reg_tit_mov_liq->id_fin_titulo_movto   = $idtitmov;
        $reg_tit_mov_liq->id_fin_titulo_liquida = $idtitliq;
        $reg_tit_mov_liq->store();
        $reg_volta = $reg_tit_mov_liq->id;
        return $reg_volta;
    }
    
    // 1-idclierp // 2-idempresa // 3-id fin titulo movto  // 4-id fin titulo liquida  
    public static function excTitMovLiq($idclierp , $idemp , $idtitmov , $idtitliq )
    {        
        $xx_titmov_liq = FinTituloMovtoLiquida::where('id_cliente_erp', '=', $idclierp)
                                            ->where('id_empresa', '=', $idemp)
                                            ->where('id_fin_titulo_movto','=', $idtitmov)
                                            ->where('id_fin_titulo_liquida','=', $idtitliq)
                                            ->delete();
        $reg_volta = 1;
        return $reg_volta;
    }
    
    
}