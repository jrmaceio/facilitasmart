<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com     |
// | esse pacote; se n�o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa		      	  |
// | 																	  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenv Boleto SICREDI: Rafael Azenha Aquini <rafael@tchesoft.com>    |
// |                        Marco Antonio Righi <marcorighi@tchesoft.com> |
// | Homologa��o e ajuste de algumas rotinas.				              |
// |                        Marcelo Belinato  <mbelinato@gmail.com> 	  |
// +----------------------------------------------------------------------+

include_once("funcoes_sicredi_aux.php");

$codigobanco = "748";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$flag_sistema = $dadosboleto["flag_sistema"];
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);
$nossonumero = $dadosboleto["nosso_numero"];
$nseq        = $dadosboleto["nseq"];

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"],10,0,"valor");
//agencia � 4 digitos
$agencia = formata_numero($dadosboleto["agencia"],4,0);
//posto da cooperativa de credito � dois digitos
$posto = formata_numero($dadosboleto["posto"],2,0);
//convenio da cooperativa 
$convenio = formata_numero($dadosboleto["convenio"],5,0);
//conta � 5 digitos
$conta = formata_numero($dadosboleto["conta"],5,0);
//dv da conta
$conta_dv = formata_numero($dadosboleto["conta_dv"],1,0);
//carteira � 2 caracteres
$carteira = $dadosboleto["carteira"];

//fillers - zeros Obs: filler1 contera 1 quando houver valor expresso no campo valor
$filler1 = 1;
$filler2 = 0;

// Byte de Identifica��o do cedente 1 - Cooperativa; 2 a 9 - Cedente
$byteidt = $dadosboleto["byte_idt"];

// Codigo referente ao tipo de cobran�a: "3" - SICREDI
$tipo_cobranca = 1; //3;

// Codigo referente ao tipo de carteira: "1" - Carteira Simples 
$tipo_carteira = 1;

if ($nossonumero == '')
{
    //nosso n�mero (sem dv) � 8 digitos
    $nnum = $dadosboleto["inicio_nosso_numero"] . $byteidt . formata_numero($dadosboleto["nseq"],5,0);
    //calculo do DV do nosso n�mero
    //$dv_nosso_numero = digitoVerificador_nossonumero("$agencia$posto$conta$nnum");
    $dv_nosso_numero = digitoVerificador_nossonumero("$agencia$posto$convenio$nnum");
    $nossonumero_dv ="$nnum$dv_nosso_numero";
    // Formata strings para impressao no boleto
    $nossonumero = substr($nossonumero_dv,0,2).'/'.substr($nossonumero_dv,2,6).'-'.substr($nossonumero_dv,8,1);
}else
{
    $nossonumero_dv = substr($nossonumero,0,2) . substr($nossonumero,3,6) . substr($nossonumero,10,1);
}
    
//forma��o do campo livre
//$campolivre = "$tipo_cobranca$tipo_carteira$nossonumero_dv$agencia$posto$conta$filler1$filler2";
$campolivre = "$tipo_cobranca$tipo_carteira$nossonumero_dv$agencia$posto$convenio$filler1$filler2";
$campolivre_dv = $campolivre . digitoVerificador_campolivre($campolivre); 

// 43 numeros para o calculo do digito verificador do codigo de barras
$dv = digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$campolivre_dv", 9, 0);

// Numero para o codigo de barras com 44 digitos
$linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$campolivre_dv";

$agencia_codigo = $agencia.".". $posto.".".$convenio; //$conta;

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;

if ($flag_sistema == 'S')
{
    $reg_return = FinTituloService::geraNossoNumero( $dadosboleto["id_titulo"] , $dadosboleto["nosso_numero"] , $dadosboleto["id_banco"] ,$dadosboleto["id_conta"] );
}


?>
