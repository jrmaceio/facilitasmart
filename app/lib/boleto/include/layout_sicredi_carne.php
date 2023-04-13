<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa		      		  |
// | 																	                                    |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenv Boleto SICREDI: Rafael Azenha Aquini <rafael@tchesoft.com>    |
// |                        Marco Antonio Righi <marcorighi@tchesoft.com> |
// | Homologação e ajuste de algumas rotinas.				               			  |
// |                        Marcelo Belinato  <mbelinato@gmail.com> 		  |
// +----------------------------------------------------------------------+
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pt-br" xml:lang="pt-br">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Boleto carne</title>
<style type="text/css">
body {margin:0;padding:0 5px;text-align: justify;color: black;background: white;font:normal normal normal 10px/1.0em "Arial", "Helvetica", "sans-serif";}
.body_pagina {width: 670px}
.body_carne  {width: 1100px}
img {border:none;margin:0;padding:0}
.instrucoes { width: 650px;background: #fff; margin: 0; padding: 10px}
.instrucoes h1 {text-align: center; font-size: 14px; margin: 0; padding: 0;}
.linha_digitavel { font-weight: bold; font-size: 12px; padding: 0 0 0 30px;line-height: 1.1em}
h5 {font-size: 8px;border-bottom: 2px dotted black; text-align: right; margin: 10px 0}
h6 {font-size: 10px;border-top: 2px dotted black; text-align: right; margin: 10px 0}
.recibo_do_sacado { width: 670px;}
.identificacao_empresa {margin: 15px 0 }
.identificacao_empresa p {padding: 0 5px; display: inline-block;margin:0}
.cabecalho_boleto {border-bottom: 2px solid black;}
.cabecalho_boleto .logo {width: 144px;}
.cabecalho_boleto p { height: 22px; padding: 0 5px; display: inline-block;margin:0;}
.cabecalho_boleto .codigo_banco {font-size: 22px; border-left: 2px solid black;border-right: 2px solid black}
.cabecalho_boleto .linha_digitavel {font-size: 14px; text-align: right; width: 430px;}
.bloquete {font-size: 9px; font-family: "Arial Narrow", "Helvetica", "sans-serif"; color: #000033;}
.bloquete div {float: left; padding: 0 5px; line-height: 1.2em;height: 25px; margin:0; border-bottom: 1px solid black;border-left: 1px solid black;vertical-align: top}
.bloquete div p {text-align:right;font-weight: bold;margin-top: 0}
.bloquete div p.p-left {text-align:left;}
.bloquete div.demonstrativo {border:none;}
p.linha_final {border:none;float:left;margin:0;padding:0;}
.codigo_barras {clear:both;border: none;height:50px;margin:0;padding:0;border-bottom: 5px solid #fff  }
.codigo_barras img {border:none;margin:0;height:50px}
.bloquete .instrucoes_caixa {width:472px; margin:0;height:130px ;padding: 0 0 0 5px ;display: inline-block;border:none;border-bottom: 1px solid black;border-left: 1px solid black;vertical-align: top}
.bloquete .instrucoes_caixa p {line-height: 1.5em;text-align:left;}
.bloquete .valores {padding: 0;margin:0;width:178px; display: inline-block}
.bloquete .sacado {height:auto;line-height: 1.1em;font-size: 1.4em;}
.bloquete_recibo_do_sacado {width:138px;border:2px solid #000;}
.carne_recibo_do_sacado {float:left;}
.carne_sacado_caixa {padding: 0 5px; width:150px;float:left;border-right: 1px dotted black;margin-right:5px;}
.carne_sacado_caixa h2 {font-size: 8px;text-align: center; margin: 5px 0; font-size:14px;line-height: 14px;border-bottom:2px solid #000}
.carne_sacado_caixa h3 {text-align: right;}
.carne_sacado_caixa h4 {font-size: 8px;text-align: left; margin: 5px 0; font-size:12px;line-height: 14px;}
.clear {clear:both;height:1px;}
.page_break {page-break-after:always;clear:both;height:1px;margin: 1px 0 5px 0;width:100%;}
.page_space {clear:both;height:1px;margin: 1px 0 5px 0;width:100%;}
.page_break_line {page-break-after:always;clear:both;height:1px;margin: 1px 0 5px 0;width:100%;border-bottom: 1px dotted #555}
.page_space_line {clear:both;height:1px;margin: 1px 0 5px 0;width:100%;border-bottom: 1px dotted #555}

</style>
 </head>
  <body class="body_carne">

  <div class="carne_sacado_caixa">
    <p><img src="app/lib/boleto/imagens/logosicredi.jpg" alt="logo banco" width="146" height="30" class="logo"/></p>

  <h3>SACADO</h3>

    <div class="bloquete bloquete_recibo_do_sacado">
      <div style="width:128px">Vencimento<br/><p><?php echo $dadosboleto["data_vencimento"]?></p></div>
      <div style="width:128px">Ag/Cod beneficiario<br/><p><?php echo $dadosboleto["agencia_codigo"]?></p></div>
      <div style="width:128px">Valor documento<br/><p><?php echo $dadosboleto["valor_boleto"]?></p></div>
      <div style="width:128px">(-) Desconto / Abatimentos<br/><p></p></div>
      <div style="width:128px">(-) Outras deducoes<br/><p></p></div>
      <div style="width:128px">(+) Mora / Multa<br/><p></p></div>
      <div style="width:128px">(+) Outros acrescimos<br/><p></p></div>
      <div style="width:128px">(=) Valor cobrado<br/><p></p></div>
      <div style="width:128px">Nosso numero<br/><p><?php echo $dadosboleto["nosso_numero"]?></p></div>
      <div style="width:128px">Numero do documento<br/><p><?php echo $dadosboleto["numero_documento"]?></p></div>
      <p class="clear">&nbsp;</p>
    </div>

    <h4>Autenticacao Mecanica<br/></h4>
    <div class="clear">&nbsp;</div>
  </div>

</div><!-- recibo_do_sacado -->
<div class="recibo_do_sacado carne_recibo_do_sacado">

  <div class="cabecalho_boleto">
    <p><img src="app/lib/boleto/imagens/logosicredi.jpg" alt="logo banco" width="108" height="45" class="logo"/></p>
    <b class="codigo_banco"><?php echo $dadosboleto["codigo_banco_com_dv"]?></b>
    <b class="linha_digitavel"><?php echo $dadosboleto["linha_digitavel"]?></b>
  </div>

  <div class="bloquete">
    <div style="width:468px">Local de Pagamento<p class="p-left">Pagavel preferencialmente nas Cooperativas do SICREDI</p></div>
    <div style="width:178px">Vencimento<p class="sacado"><?php echo $dadosboleto["data_vencimento"]?></p></div>
  </div>

  <div class="bloquete">
    <div style="width:468px">Beneficiario
    <p class="p-left"><?php echo $dadosboleto["cedente"]; ?> <?php echo isset($dadosboleto["cpf_cnpj"]) ? $dadosboleto["cpf_cnpj"] : '' ?><br>
	</p>
    </div>
  <div style="width:178px">Agencia/Codigo do beneficiario
  <p><?php echo $dadosboleto["agencia_codigo"]?></p></div>
  </div>

  <div class="bloquete">
    <div style="width:122px">Data Documento<p><?php echo $dadosboleto["data_documento"]?></p></div>
    <div style="width:122px">Numero do documento<p class="sacado"><?php echo $dadosboleto["numero_documento"]?></p></div>
    <div style="width:60px">Especie<p><?php echo $dadosboleto["especie_doc"]?></p></div>
    <div style="width:30px">Aceite<p><?php echo $dadosboleto["aceite"]?></p></div>
    <div style="width:90px">Data Processamento<p><?php echo $dadosboleto["data_processamento"]?></p></div>
    <div style="width:178px">Nosso numero<p class="sacado"><?php echo $dadosboleto["nosso_numero"]?></p></div>
  </div>

  <div class="bloquete">
    <div style="width:122px">Uso do Banco<p></p></div>
    <div style="width:92px">Carteira<p><?php echo $dadosboleto["carteira"]?></p></div>
    <div style="width:60px">Moeda<p><?php echo $dadosboleto["especie"]?></p></div>
    <div style="width:60px">Quantidade<p></p></div>
    <div style="width:90px">Valor documento<p></p></div>
    <div style="width:178px">(=) Valor documento<p class="sacado"><?php echo $dadosboleto["valor_boleto"]?></p></div>
  </div>

  <div class="bloquete">
    <div class="instrucoes_caixa">
      Instrucoes (Texto de responsabilidade do cedente)
      <p style="width:564px" class="demonstrativo p-left"><br/>	  
        <strong><?php echo $dadosboleto["instrucoes1"]; ?></strong><br/>
        <strong><?php echo $dadosboleto["instrucoes2"]; ?></strong><br/>
        <strong><?php echo $dadosboleto["instrucoes3"]; ?></strong><br/>
		<strong><?php echo $dadosboleto["instrucoes4"]; ?></strong><br/>
		<strong></strong><br/><br />
		<strong></strong>
      </p>
    </div>
    <div class="valores">
      <div style="width:178px">(-) Desconto / Abatimentos<p></p></div>
      <div style="width:178px">(-) Outras deducoes
      <p></p></div>
      <div style="width:178px">(+) Mora / Multa<p></p></div>
      <div style="width:178px">(+) Outros acrescimos
      <p></p></div>
      <div style="width:178px">(=) Valor cobrado<p></p></div>
    </div>

  </div>

  <div class="bloquete">
    <div style="width:468px" class="sacado">Pagador<br/>
      <strong><?php echo $dadosboleto["sacado"]?></strong><br/>
      <strong><?php echo $dadosboleto["endereco1"]?></strong><br/>
      <strong><?php echo $dadosboleto["endereco2"]?></strong>
    </div>
    <div style="width:178px" class="sacado"><br/><br/><br/>Cod. Baixa</div>
  </div>

  <p style="width:410px" class="linha_final">Pagador/Avalista&nbsp;&nbsp;&nbsp;<strong></strong></p>
  <p style="width:130px" class="linha_final">Autenticacao mecanica</p>
  <p style="width:130px;text-align:right;" class="linha_final"><strong>FICHA DE COMPENSACAO</strong></p>
  <div class="codigo_barras">
  <TABLE cellSpacing=0 cellPadding=0 width=666 border=0>
	
		<TR>
			<TD vAlign=bottom align=left height=50 class=cp><?php fbarcode($dadosboleto["codigo_barras"]); ?> </script>
			</TD>
		</tr>
	
</table>
<div class="clear">&nbsp;</div>
</div>
 
</div><!-- recibo_do_sacado -->

<div class="clear">&nbsp;</div>

<div class="page_space">&nbsp;</div>

 </body>
</html>




