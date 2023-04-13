<?php
/**
 * ReceitaAtualChartView
 * @author  <your name here>
 */
class ReceitaAtualChartView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct( $show_breadcrumb )
    {
        parent::__construct();
        
        try
        {
            $html = new THtmlRenderer('app/resources/google_pie_chart.html');
            
            TTransaction::open('facilitasmart');
            
            $condominio_id = TSession::getValue('id_condominio');

            $datahoje = date('Y-m-d');

            $partes = explode("-", $datahoje);
        
            $ano_hoje = $partes[0];
            $mes_hoje = $partes[1];
            $dia_hoje = $partes[2];

            $dt_creditoIni = $ano_hoje . '-' . $mes_hoje . '-01';
            $dt_creditoFim = date('Y-m-d', strtotime('+5 days', strtotime($datahoje))); // considera compensação
            //var_dump($dt_creditoFim);

            $data = array();
            $data[] = [ 'Arrecadação', 'Valor' ];

            $mesref = $mes_hoje.'/'.$ano_hoje;

            $conn0 = TTransaction::get();
            $result0 = $conn0->query("SELECT *
                                  FROM fechamento
                                  WHERE 
                                  mes_ref = '{$mesref}' and condominio_id = {$condominio_id}");

               
            if ($result0)
            {
                foreach ($result0 as $fechamento)
                {
                    $data[] = ['Previsto ' . 'R$ ' . number_format($fechamento['previsao_arrecadacao'], 2, ',', '.'), (float) $fechamento['previsao_arrecadacao']];
                    
                 }
            }

            $conn = TTransaction::get();
            $result = $conn->query("SELECT sum(valor_pago) as valor_pago
                                 FROM contas_receber
                                 WHERE dt_liquidacao between '{$dt_creditoIni}' and '{$dt_creditoFim}' 
                                 and situacao = 1
                                 and mes_ref = '{$mesref}'  
                                 and condominio_id = {$condominio_id}");
        
            //var_dump($result);
            if ($result)
            {
                foreach ($result as $row)
                {
                    $data[] = ['Realizado ' . 'R$ ' . number_format($row['valor_pago'], 2, ',', '.'), (float) $row['valor_pago']];
                 }
            }
            
            TTransaction::close();
            
            //var_dump($receitas_mes);

            //foreach ($receitas_mes as $mes => $valor)
            //{
            //    $data[] = [ $meses[ (int)$mes], $valor ];
            //}
            
            $panel = new TPanelGroup('Receita / Atual - ' . $mesref);
            $panel->style = 'width:100%';
            $panel->add($html);
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Recebimentos até o momento',
                                               'ytitle' => 'Receitas',
                                               'xtitle' => 'Mês'));
            $container = new TVBox;
            $container->style = 'width: 100%';
            if ($show_breadcrumb)
            {
                $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            }
            $container->add($panel);
            parent::add($container);

            //file_put_contents('app/output/customers.html', $panel);
            //TPage::openFile('app/output/customers.html');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}