<?php
class FinBoletoView extends TPage
{
    public function onGenerate($param)
    {
        $dadosboletoV = $param;

        $rotina = 'ContasReceberListagem';  // temporario 

        ob_start();
        if (!isset($_GET['print']) OR ($_GET['print'] !== '1'))
        {
            $url = $_SERVER['QUERY_STRING'];
            //echo "<center> <a href='' onclick='window.open(\"engine.php?{$url}&print=1\")'> <h1>Clique aqui para Imprimir</h1></a> </center>";
            
            if ($dadosboletoV["rotina"] == 'SegundaViaBoletos') { $rotina = 'SegundaViaBoletos'; }
            if ($dadosboletoV["rotina"] == 'ContasReceberListagemAux') { $rotina = 'ContasReceberListagem'; }
            
            echo "<script>window.open(\"engine.php?{$url}&print=1\")</script>";
            echo "<script>location.assign(\"index.php?class={$rotina}\")</script>";
            
        }

    // inicio foreach
    foreach ($dadosboletoV as $keyV=>$value_dadosboletoV)
    {
        if ( ($keyV != 'class') AND ($keyV != 'method') AND ($keyV != 'print') )
        {
        
        $dadosboleto["flag_sistema"]     = $value_dadosboletoV["flag_sistema"];

        $dadosboleto["rotina"]           = $value_dadosboletoV["rotina"];
        $dadosboleto["id_titulo"]        = $value_dadosboletoV["id_titulo"];
        $dadosboleto["nseq"]             = $value_dadosboletoV["nseq"];
        $dadosboleto["nosso_numero"]     = $value_dadosboletoV["nosso_numero"];
        $dadosboleto["numero_documento"] = $value_dadosboletoV["numero_documento"];

        $vlr_conv = $value_dadosboletoV["valor_boleto"];
        $vlr_conv = str_replace(".", "",$vlr_conv);
        $vlr_conv = str_replace(",", ".",$vlr_conv);
        $vlr_conv = number_format($vlr_conv, 2, ',', '');
        $dadosboleto["valor_boleto"]     = $vlr_conv;

        $dadosboleto["agencia"]        = $value_dadosboletoV["agencia"];
        $dadosboleto["posto"]          = $value_dadosboletoV["posto"];
        $dadosboleto["convenio"]       = $value_dadosboletoV["convenio"];
        $dadosboleto["conta"]          = $value_dadosboletoV["conta"];
        $dadosboleto["conta_dv"]       = $value_dadosboletoV["conta_dv"];
        $dadosboleto["carteira"]       = $value_dadosboletoV["carteira"];
        $dadosboleto["byte_idt"]       = $value_dadosboletoV["byte_idt"];
        $dadosboleto["id_banco"]       = $value_dadosboletoV["id_banco"];
        $dadosboleto["id_conta"]       = $value_dadosboletoV["id_conta"];
        $dadosboleto["identificacao"]  = $value_dadosboletoV["identificacao"];
        $dadosboleto["endereco"]       = $value_dadosboletoV["endereco"];
        $dadosboleto["cidade_uf"]      = $value_dadosboletoV["cidade_uf"];
        $dadosboleto["cedente"]        = $value_dadosboletoV["cedente"];
        $dadosboleto["especie"]        = $value_dadosboletoV["especie"];
        $dadosboleto["quantidade"]     = $value_dadosboletoV["quantidade"];
        $dadosboleto["cpf_cnpj"]       = $value_dadosboletoV["cpf_cnpj"];
        $dadosboleto["sacado"]         = $value_dadosboletoV["sacado"];
        $dadosboleto["especie_doc"]    = $value_dadosboletoV["especie_doc"];
        $dadosboleto["aceite"]         = $value_dadosboletoV["aceite"];
        $dadosboleto["valor_unitario"] = $value_dadosboletoV["valor_unitario"];
        $dadosboleto["endereco1"]      = $value_dadosboletoV["endereco1"];
        $dadosboleto["endereco2"]      = $value_dadosboletoV["endereco2"];
        $dadosboleto["inicio_nosso_numero"] = $value_dadosboletoV["inicio_nosso_numero"];
        $dadosboleto["data_vencimento"]    = $value_dadosboletoV["data_vencimento"];

        $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
        $dadosboleto["data_documento"]     = date("d/m/Y"); // Data de processamento do boleto (opcional)
        
        $demonstrativo = explode("\n", $value_dadosboletoV['demonstrativo']);
        for ($n=0; $n<=2; $n++)
        {
            $key = $n+1;
            $texto = isset($demonstrativo[$n]) ? $demonstrativo[$n] : '';
            $dadosboleto["demonstrativo{$key}"] = $texto;
        }
        $instrucoes = explode("\n", $value_dadosboletoV['instrucoes']);
        for ($n=0; $n<=3; $n++)
        {
            $key = $n+1;
            $texto = isset($instrucoes[$n]) ? $instrucoes[$n] : '';
            $dadosboleto["instrucoes{$key}"] = $texto;
        }
        
        if ($value_dadosboletoV["flag_sistema"] == 'D')
        {
            if ($value_dadosboletoV['codigo_banco'] == 1)    //Banco do Brasil
            {
                // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
                $dadosboleto["quantidade"] = "";
                $dadosboleto["valor_unitario"] = "";
                $dadosboleto["aceite"] = "N";        
                $dadosboleto["especie"] = "R$";
                $dadosboleto["especie_doc"] = "DM";
                // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
                $dadosboleto["agencia"] = "8888";
                $dadosboleto["conta"] = "88888888";
                $dadosboleto["convenio"] = "888888";
                $dadosboleto["contrato"] = "888888";
                $dadosboleto["carteira"] = "88";
                $dadosboleto["variacao_carteira"] = "-019";    
                // TIPO DO BOLETO
                $dadosboleto["formatacao_convenio"] = "7";
                $dadosboleto["formatacao_nosso_numero"] = "2";
                // SEUS DADOS
                $dadosboleto["identificacao"] = "Sacador";
                $dadosboleto["cpf_cnpj"] = "12.222.333/0001-24";
                $dadosboleto["endereco"] = "Av. Bento Gonçalves, 123. Bairro Centro - Cep 88.888-888";
                $dadosboleto["cidade_uf"] = "Porto Alegre - RS";
                $dadosboleto["cedente"] = "Empresa LTDA - ME";
            }

            if ($value_dadosboletoV['codigo_banco'] == 748)    //sicredi
            {
                //$dadosboleto["posto"] = "88";              //alterar
                //$dadosboleto["conta_dv"] = "88";           //alterar
                //$dadosboleto["byte_idt"] = "8";            //alterar
                //$dadosboleto["inicio_nosso_numero"] = "8"; //alterar
            
                /*            
                // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
                $dadosboleto["quantidade"] = "";
                $dadosboleto["valor_unitario"] = "";
                $dadosboleto["aceite"] = "N";        
                $dadosboleto["especie"] = "R$";
                $dadosboleto["especie_doc"] = "DM";
                // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
                $dadosboleto["agencia"] = "8888";
                $dadosboleto["conta"] = "88888888";
                $dadosboleto["convenio"] = "888888";
                $dadosboleto["contrato"] = "888888";
                $dadosboleto["carteira"] = "88";
                $dadosboleto["variacao_carteira"] = "-019";    
                // TIPO DO BOLETO
                $dadosboleto["formatacao_convenio"] = "7";
                $dadosboleto["formatacao_nosso_numero"] = "2";
                */
                // SEUS DADOS
                //$dadosboleto["identificacao"] = "Sacador";
                //$dadosboleto["cpf_cnpj"] = "12.222.333/0001-24";
                //$dadosboleto["endereco"] = "Av. Bento Gonçalves, 123. Bairro Centro - Cep 88.888-888";
                //$dadosboleto["cidade_uf"] = "Porto Alegre - RS";
                //$dadosboleto["cedente"] = "Empresa LTDA - ME";
            }

            if ($value_dadosboletoV['codigo_banco'] == 756)    //sicoob
            {
                $dadosboleto["modalidade_cobranca"] = "88";      //alterar
                $dadosboleto["numero_parcela"] = "88";           //alterar
                /*            
                // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
                $dadosboleto["quantidade"] = "";
                $dadosboleto["valor_unitario"] = "";
                $dadosboleto["aceite"] = "N";        
                $dadosboleto["especie"] = "R$";
                $dadosboleto["especie_doc"] = "DM";
                // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
                $dadosboleto["agencia"] = "8888";
                $dadosboleto["conta"] = "88888888";
                $dadosboleto["convenio"] = "888888";
                $dadosboleto["contrato"] = "888888";
                $dadosboleto["carteira"] = "88";
                $dadosboleto["variacao_carteira"] = "-019";    
                // TIPO DO BOLETO
                $dadosboleto["formatacao_convenio"] = "7";
                $dadosboleto["formatacao_nosso_numero"] = "2";
                */
                // SEUS DADOS
                $dadosboleto["identificacao"] = "Sacador";
                $dadosboleto["cpf_cnpj"] = "12.222.333/0001-24";
                $dadosboleto["endereco"] = "Av. Bento Gonçalves, 123. Bairro Centro - Cep 88.888-888";
                $dadosboleto["cidade_uf"] = "Porto Alegre - RS";
                $dadosboleto["cedente"] = "Empresa LTDA - ME";
            }
        } // fim if ($dadosboleto["flag_sistema"] == 'D')

        /*
        ob_start();
        if (!isset($_GET['print']) OR ($_GET['print'] !== '1'))
        {
            $url = $_SERVER['QUERY_STRING'];
            //echo "<center> <a href='' onclick='window.open(\"engine.php?{$url}&print=1\")'> <h1>Clique aqui para Imprimir</h1></a> </center>";
            
            if ($dadosboleto["rotina"] == 'SegundaViaBoletos') { $rotina = 'SegundaViaBoletos'; }
            if ($dadosboleto["rotina"] == 'ContasReceberListagemAux') { $rotina = 'ContasReceberListagem'; }
            
            echo "<script>window.open(\"engine.php?{$url}&print=1\")</script>";
            echo "<script>location.assign(\"index.php?class={$rotina}\")</script>";
            
        }
        */
                
        if ($value_dadosboletoV["flag_sistema"] == 'D')
        {
            include("app/lib/boleto/include/funcoes_bb.php");
            include("app/lib/boleto/include/layout_bb.php");
        }
        
        if ($value_dadosboletoV["flag_sistema"] == 'S')
        {
            if ($value_dadosboletoV['codigo_banco'] == 1)    //Banco do Brasil
            {
                if ($value_dadosboletoV['modelo'] == 1)      //Tipo boleto Clássico
                {
                    include("app/lib/boleto/include/funcoes_bb.php");
                    include("app/lib/boleto/include/layout_bb.php");
                }
                if ($value_dadosboletoV['modelo'] == 2)     //Tipo boleto Carnê
                { 
                }
                if ($value_dadosboletoV['modelo'] == 3)     //Tipo boleto Informativo
                { 
                }
            } // fim banco brasil
            
            if ($value_dadosboletoV['codigo_banco'] == 748)    //Sicredi
            {
                if ($value_dadosboletoV['modelo'] == 1)        //Tipo boleto Clássico
                {
                    include("app/lib/boleto/include/funcoes_sicredi.php");
                    include("app/lib/boleto/include/layout_sicredi.php");
                }
                if ($value_dadosboletoV['modelo'] == 2)        //Tipo boleto carne
                {
                    include("app/lib/boleto/include/funcoes_sicredi.php");
                    include("app/lib/boleto/include/layout_sicredi_carne.php");
                }
            } // fim sicredi
    
    
            if ($value_dadosboletoV['codigo_banco'] == 756)    //Sicoob
            {
                if ($value_dadosboletoV['modelo'] == 1)        //Tipo boleto Clássico
                {
                    include("app/lib/boleto/include/funcoes_bancoob.php");
                    include("app/lib/boleto/include/layout_bancoob.php");
                }
            } // fim sicoob
    
        } // fim if ($value_dadosboletoV["flag_sistema"] == 'S')


     }        
        
     }   
    // final foreach //foreach ($dadosboleto_vem as $key_vem=>$value_dadosboleto_vem)            
        
        if (isset($_GET['print']) AND ($_GET['print'] === '1'))
        {
            echo '<script>window.print();</script>';
        }
        $content = ob_get_clean();
        
        parent::add($content);
    }
}
?>
