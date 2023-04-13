<?php
class FinTituloMovtoBusca
{
    // 1 - idfintitulo // 2 - dt base titulo ate // 3 - idclierp // 4 - idempresa
    public static function setBusca($idfintitulo , $dtbasetit , $idclierptit , $idemptit)      
    {
        $reg_fintit = new FinTitulo($idfintitulo); // registro fintitulo
        $id_moeda = $reg_fintit->id_moeda;
        $valor_cotacao = MoedaCotacaoBusca::MoeBusca($id_moeda , $dtbasetit);  // -- busca cotacao moeda //
        $vlrsld = $vlrorig = $vlrcor = $vlrliq = $vlrjur = $vlrdes = $vlrdev = $vlrsub = 0;
        // -- busca movimento titulo 
        $reg_fintitmov = FinTituloMovto::where('id_fin_titulo', '=', $idfintitulo)
                                        ->where('id_cliente_erp', '=', $idclierptit)
                                        ->where('id_empresa', '=', $idemptit)
                                        ->where('dt_movto', '<=', $dtbasetit)
                                        ->load();
                                        
        foreach ($reg_fintitmov as $value)
        {
            if ($value->id_tipo_liquidacao == 1)
            {
                $mul = 1;
                $vlrsld = $vlrsld + ( $value->vlr_movto * $mul);
                $vlrorig = $value->vlr_movto;
            }
            if ($value->id_tipo_liquidacao == 2)
            {
                $mul = -1;
                $vlrsld = $vlrsld + ( $value->vlr_movto * $mul);
                $vlrliq = $vlrliq + $value->vlr_movto;
            }            
            if ($value->id_tipo_liquidacao == 3)
            {
                $mul = 1;
                $vlrjur = $vlrjur + $value->vlr_movto;
            }
            if ($value->id_tipo_liquidacao == 4)
            {
                $mul = -1;
                $vlrsld = $vlrsld + ( $value->vlr_movto * $mul);
                $vlrdes = $vlrdes + $value->vlr_movto;
            }
            if ($value->id_tipo_liquidacao == 5)
            {
                $mul = -1;
                $vlrsld = $vlrsld + ( $value->vlr_movto * $mul);
                $vlrdev = $vlrdev + $value->vlr_movto;
            }             
            if ($value->id_tipo_liquidacao == 6)
            {
                $mul = -1;
                $vlrsld = $vlrsld + ( $value->vlr_movto * $mul);
                $vlrsub = $vlrsub + $value->vlr_movto;
            }             
        }

        // --            
        $vlrcor = $vlrsld * $valor_cotacao;
                 
        $reg_volta = array();
        $reg_volta = (object) $reg_volta;
        
        $reg_volta->vlrsld  = $vlrsld;
        $reg_volta->vlrorig = $vlrorig;
        $reg_volta->vlrcor  = $vlrcor;
        $reg_volta->vlrliq  = $vlrliq;
        $reg_volta->vlrjur  = $vlrjur;
        $reg_volta->vlrdes  = $vlrdes;
        $reg_volta->vlrdev  = $vlrdev;
        $reg_volta->vlrsub  = $vlrsub;
        
        return $reg_volta;
   }
   
}

        /*
        // -- TITULO 20 --
        $idtpliq = TipoLiquidacao::FINTIT;
        // -- TITULO 50 --
        $idtpliq = TipoLiquidacao::FINLIQ;
        // -- TITULO 60 --
        $idtpliq = TipoLiquidacao::FINJUR;
        // -- TITULO 70 --
        $idtpliq = TipoLiquidacao::FINDES;
        // -- TITULO 80 --
        $idtpliq = TipoLiquidacao::FINDEV;
        // -- TITULO 90 --
        $idtpliq = TipoLiquidacao::FINSUB;
        */

