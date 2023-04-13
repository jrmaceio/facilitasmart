<?php

/**
 * CLASSE RESPONSAVEL POR RECEBER O PRAZO E O VALOR TOTAL 
 * A CLASSE REALIZA A DIVISAO DAS PARCELAS RETORNANDO UM ARRAY DE OBJETOS COM OS SEGUINTES ATRIBUTOS:
 * 
 *  -> parcela              EX: 1/3
 *  -> valor_parcela        EX: R$ 500.00 
 *  -> vencimento_parcela   EX: 05/06/2016
 * @author Danilo Ribeiro <daniloemanuel@msn.com>
 */
class GerarParcelas extends TPage {

    /**
     * 
     * @param float / double $ValorTotal
     * @param string Ex: '30/60/90' $Prazo
     * @param Pode ser Null ou String. Ex: 05/06/2016 $DataPrimeiraParcela
     * @return \stdClass - Um Array de Objetos
     */
    public static function calcularParcelas($ValorTotal, $Prazo, $DataPrimeiraParcela = null) {

        //SE OS PARAMETROS ValorTotal E Prazo NAO FOREM VAZIOS, EXECUTA O METODO
        if (!empty($ValorTotal) && !empty($Prazo)) {

            //SE O USER NAO PASSAR A DATA DO PRIMEIRO VENCIMENTO, ELE USA A DATA DO DIA 
            if ($DataPrimeiraParcela != null) {
                $DataPrimeiraParcela = explode("/", $DataPrimeiraParcela);
                $dia = $DataPrimeiraParcela[0];
                $mes = $DataPrimeiraParcela[1];
                $ano = $DataPrimeiraParcela[2];
            } else {
                $dia = date("d");
                $mes = date("m");
                $ano = date("Y");
            }
            //TRANSFORMA A STRING PRAZO (30/60/90) EM UM ARRAY E CONTA QTOS INDICES; O NUMERO DE INDICES SERÃ O NUMERO DE PARCELAS 
            $totalParcelas = count(explode('/', $Prazo));
            $auxTotParcelas = $totalParcelas;
            //INICIALIZA COM ZERO A VARIAVEL AUXILIAR SOMA, ESSA VARIAVEL SERA UTIL PARA QUE A DIVISAO SEJA EXATA, CASO HAJA RESIDUO
            $soma = 0;
            //LACO PARA CRIAR O OBSETO QUE SERA RETORNADO E REALIZAR OS CALCULOS
            for ($i = 0; $i < $totalParcelas; $i++) {
                $numeroParcela = $i + 1;
                $objeto[] = new stdClass;
                $objeto[$i]->detail_id = 'X';
                $objeto[$i]->id = empty($data->detail_id) ? 'X' . mt_rand(1000000000, 1999999999) : $data->detail_id;;
                $objeto[$i]->numero_parcela = $numeroParcela . '/' . $auxTotParcelas;
                $objeto[$i]->valor_parcela = $parcela = round($ValorTotal / $totalParcelas, 2);
                $objeto[$i]->vencimento_parcela = date("d/m/Y", strtotime("+" . $i . " month", mktime(0, 0, 0, $mes, $dia, $ano)));
                $soma += $parcela;

            }

            //APOS OS CALCULOS, VERIFICA-SE SE HOUVE RESIDUOS, SE HOUVE, ADICIONA-OS NA ULTIMA PARCELA
            if ($soma !== $ValorTotal) {
                $objeto[$totalParcelas - 1]->valor_parcela = $objeto[$totalParcelas - 1]->valor_parcela + ( $ValorTotal - $soma);
            }

     

            //VERIFICA SE O OBJETO EXISTE SE SIM, O RETORNA, SE NAO RETORNA NULL
            if ($objeto) {
                return $objeto;
            } else {
                return null;
            }
        } else {
            new TMessage('info', 'Veja se os Valores Foram Passados corretamente nos argumentos para o calculo');
        }
    }

}

