<?php

class Boleto extends TWindow
{
    public function onGenerate($param)
    {
        //var_dump($param);
        
        $dadosboleto = $param;
        
        $this->string = new StringsUtil;
        
        var_dump($dadosboleto);
        
        //try
        //{
          TTransaction::open('facilita');
          
          // get the parameter $key
         /////// $key   = $param['key']; // contas a receber - id = key
          
         //////////// $object = new ContasReceber($key); // instantiates the Active Record
                 
          $unidade_desc = Unidades::RetornaDescricaoUnidade($object->unidade_id);
          $imovel_id = TSession::getValue('id_imovel');
          
           // paga os dados de banco, conta corrente
          $conn = TTransaction::get();
          $result = $conn->query("select codigo_banco,
                                  agencia, conta, dv_conta, cedente, carteira, especie_doc_boleto,
                                  especie_doc_remessa, dias_protesto, dias_devolucao,
                                  producao, conta_com_cod_cedente
                                  from conta_corrente
                                  INNER JOIN banco on conta_corrente.banco_id = banco.id   
                                  where imovel_id = '{$imovel_id}'
                               ");
       
           var_dump($result);
           
          foreach ($result as $row)
          {
            $conta_corrente = $row;
          }
          
          // dados do imovel
          $conn = TTransaction::get();
          $result = $conn->query("select * from imoveis where id = '{$imovel_id}'
                               ");
       
          foreach ($result as $row)
          {
            $imovel = $row;
          }
         
          // dados do proprietario
          $conn = TTransaction::get();
          $result = $conn->query("select b.nome, b.endereco, b.bairro, b.cidade, b.estado, b.cep
                                from unidades as a
                                inner join pessoas as b 
                                on a.proprietario_id =  b.id
                                where a.id = {$object->unidade_id}");
      
          foreach ($result as $row)
          {   
            $proprietario = $row;
          }
           
          $unidade_prop_nome = $proprietario['nome'];
          
          // dados do boleto
          //existe uma view, diferença dela é que lá ela agrupa para formar o valor do boleto
          $conn = TTransaction::get();
          $result = $conn->query("SELECT 
	          contas_receber.id as receber_id, 
	          contas_receber.mes_ref as receber_mes_ref, 
            contas_receber.valor as receber_valor, 
            contas_receber.imovel_id as receber_imovel_id,
            contas_receber.dt_vencimento as receber_dt_vencimento,
            contas_receber.dt_lancamento as receber_dt_lancamento,
            contas_receber.cobranca as receber_cobranca,
            unidades.id as unidade_id, 
            unidades.descricao as unidade_descricao, 
            pessoas.nome as pes_nome,
            pessoas.endereco as pes_end,
            pessoas.bairro as pes_bairro,
            pessoas.cidade as pes_cidade,
            pessoas.estado as pes_estado,
            pessoas.cep    as pes_cep,
            conta_corrente.agencia as cc_agencia,
            conta_corrente.conta as cc_conta,
            conta_corrente.dv_conta as cc_dv_conta,
            conta_corrente.cedente as cc_cedente,
            conta_corrente.carteira as cc_carteira,
            conta_corrente.especie_doc_boleto as cc_especie_doc_boleto,
            conta_corrente.especie_doc_remessa as cc_especie_doc_remessa,
            conta_corrente.dias_protesto as cc_dias_protesto,
            conta_corrente.dias_devolucao as cc_dias_devolucao,
            conta_corrente.producao as cc_producao,
            conta_corrente.conta_com_cod_cedente as cc_conta_com_cod_cedente,
            imoveis.resumo as imovel_resumo,
            imoveis.nome as imovel_nome,
            imoveis.endereco as imovel_endereco,         
            imoveis.bairro as imovel_bairro,
            imoveis.cidade as imovel_cidade,
            imoveis.estado as imovel_estado,
            imoveis.cep as imovel_cep
            FROM contas_receber 
              INNER JOIN imoveis on contas_receber.imovel_id = imoveis.id 
              INNER JOIN unidades on contas_receber.unidade_id = unidades.id 
              INNER JOIN pessoas on unidades.proprietario_id = pessoas.id 
              INNER JOIN conta_corrente on contas_receber.imovel_id = conta_corrente.imovel_id 
              INNER JOIN banco on conta_corrente.banco_id = banco.id 
            where 
              contas_receber.situacao = '0' 
              and conta_corrente.producao = 'S'  
              and contas_receber.unidade_id = 34 
              and contas_receber.dt_vencimento = '2017-03-29' 
              and contas_receber.cobranca = '1'
                                 ");
          
          $valor_boleto = 0;
          $object = array();
          
          var_dump($result);
          foreach ($result as $row)
          {
            $valor_boleto += $row['receber_valor'];
            $object = $row;  // dados para a geracao do boleto
          }
           
          var_dump($object);
          //var_dump($valor_boleto);
          
          TTransaction::close();
          
        //}
        //catch(Exception $e)
        //{
       //     new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
       // }
        
        /*
        // conversoes
        //$object->dt_lancamento ? $object->dt_lancamento = $this->string->formatDateBR($object->dt_lancamento) : null;
        //$object->dt_vencimento ? $object->dt_vencimento = $this->string->formatDateBR($object->dt_vencimento) : null;
        //$object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                
        // DADOS DO BOLETO PARA O SEU CLIENTE
        $dias_de_prazo_para_pagamento = 5;
        $taxa_boleto = 2.95;
        
        $data_venc = $object['receber_dt_vencimento'];
        
        // Composição Nosso Numero - CEF SIGCB
        $dadosboleto["nosso_numero1"] = "000"; // tamanho 3
        $dadosboleto["nosso_numero_const1"] = "2"; //constanto 1 , 1=registrada , 2=sem registro
        $dadosboleto["nosso_numero2"] = "000"; // tamanho 3
        $dadosboleto["nosso_numero_const2"] = "4"; //constanto 2 , 4=emitido pelo proprio cliente
        $dadosboleto["nosso_numero3"] = "000179262"; // tamanho 9
                
        $dadosboleto["numero_documento"] = $object['receber_unidade_desc'];	
        
        $dadosboleto["data_vencimento"] = $object['receber_dt_vencimento']; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"] = $object['receber_dt_lancamento']; // Data de emissão do titulo
        $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional) - data emissao do boleto
        
        $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
        
        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"] = ' (' . $object['receber_unidade_id'] . ') ' . $unidade_desc . ' - ' . $proprietario['nome'];
        $dadosboleto["endereco1"] = $proprietario['endereco'].','.$proprietario['bairro'];
        $dadosboleto["endereco2"] = $proprietario['cidade'].','.$proprietario['estado'].','.$proprietario['cep'];;
        
        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Nonononono";
        $dadosboleto["demonstrativo2"] = "Mensalidade referente a nonon nonooon nononon<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
        $dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";

        // INSTRUÇÕES PARA O CAIXA
        $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
        $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
        $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: xxxx@xxxx.com.br";
        $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto BoletoPhp - www.boletophp.com.br";

        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"] = "";
        $dadosboleto["valor_unitario"] = "";
        $dadosboleto["aceite"] = "";		
        $dadosboleto["especie"] = "R$";
        $dadosboleto["especie_doc"] = "";

        // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
        // DADOS DA SUA CONTA - CEF
        $dadosboleto["agencia"] = $conta_corrente['agencia']; // Num da agencia, sem digito
        $dadosboleto["conta"] = $conta_corrente['conta']; 	// Num da conta, sem digito
        $dadosboleto["conta_dv"] = $conta_corrente['dv_conta']; 	// Digito do Num da conta

        // DADOS PERSONALIZADOS - CEF
        $dadosboleto["conta_cedente"] = $conta_corrente['cedente']; // Código Cedente do Cliente, com 6 digitos (Somente Números)
        $dadosboleto["carteira"] = "SR";  // Código da Carteira: pode ser SR (Sem Registro) ou CR (Com Registro) - (Confirmar com gerente qual usar)
        
        // DADOS PERSONALIZADOS - SICREDI
//        $dadosboleto["posto"]= "18";      // Código do posto da cooperativa de crédito
  //      $dadosboleto["byte_idt"]= "2";	  // Byte de identificação do cedente do bloqueto utilizado para compor o nosso número.
                                  // 1 - Idtf emitente: Cooperativa | 2 a 9 - Idtf emitente: Cedente
    //    $dadosboleto["carteira"] = "A";   // Código da Carteira: A (Simples) 

        // SEUS DADOS -  cabecalho
        $dadosboleto["identificacao"] = "Facilita Home Service - Telefones (82) 4102-0015 / 99994-3552";
        $dadosboleto["cpf_cnpj"] = ''; // vazio, aparece no boleto parte superior
        $dadosboleto["endereco"] = $imovel['endereco'].', '.$imovel['bairro'];
        $dadosboleto["cidade_uf"] = $imovel['cidade'].', '.$imovel['estado'].', '.$imovel['cep'];
        
        //nome no boleto
        $dadosboleto["cedente"] = $imovel['nome'];
 
        ob_start();
        
        if (!isset($_GET['print']) OR ($_GET['print'] !== '1'))
        {
            $url = $_SERVER['QUERY_STRING'];
            echo "<center> <a href='' onclick='window.open(\"engine.php?{$url}&print=1\")'> 
            <h1>Clique aqui para Imprimir</h1></a> </center>";

        }
        
        // NÃO ALTERAR!
        include("lib2/boleto/include/funcoes_cef_sigcb.php");
        include("lib2/boleto/include/layout_cef.php");
       
        // com layoutu corrigido para aceitar conversao para pdf
       // include("lib2/boleto/include/funcoes_sicredi2.php");
       // include("lib2/boleto/include/layout_sicredi2.php");

        //chama a impressora
        //if (isset($_GET['print']) AND ($_GET['print'] === '1'))
        //{
        //    echo '<script>window.print();</script>';
        //} 
        
        $content = ob_get_clean();

        
// convert

    
        
      parent::add($content);

*/          
    }
}