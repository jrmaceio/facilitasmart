<?php
/**
 * ReceitaMesChartView
 * @author  <your name here>
 */
class ReceitaMesChartView extends TPage
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
            $html = new THtmlRenderer('app/resources/google_bar_chart.html');
            
            $meses = [ 1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',     4 => 'Abril',    5 => 'Maio',      6 => 'Junho',
                       7 => 'Julho',   8 => 'Agosto',    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro' ];
            
            $data = array();
            $data[] = [ 'Mês', 'Valor' ];
            
            TTransaction::open('facilitasmart');
            $receitas_mes = ContasReceber::getReceitaMes( date('Y'), TSession::getValue('id_condominio') );
            TTransaction::close();
            
            //var_dump($receitas_mes);

            foreach ($receitas_mes as $mes => $valor)
            {
                $data[] = [ $meses[ (int)$mes], (float) $valor ];
                
            }
            
            $panel = new TPanelGroup('Receita / mês - ' . date('Y'));
            $panel->style = 'width:100%';
            $panel->add($html);
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Receitas por mês',
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
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}