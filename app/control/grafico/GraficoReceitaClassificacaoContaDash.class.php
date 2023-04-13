<?php
/**
 * Chart
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class GraficoReceitaClassificacaoContaDash extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct( $show_breadcrumb = true )
    {
        parent::__construct();
        
        $string = new StringsUtil;

        $html = new THtmlRenderer('app/resources/google_pie_chart.html');
        
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        TTransaction::open('facilitasmart');
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        foreach ($users as $user)
        {
            $nivel_acesso = $user->nivel_acesso_inf;
            $condominio_id = $user->condominio_id;
        }
 
        //var_dump($nivel_acesso);
        if ($nivel_acesso == '1') { // administradora
            $condominio_id = TSession::getValue('id_condominio');
 
        }

        if ($nivel_acesso == '0') { // administradora
            $condominio_id = TSession::getValue('id_condominio');
 
        }
        
        // dados do fechamento
        $conn = TTransaction::get();
        $colunas = $conn->query("SELECT * FROM fechamento 
                                 WHERE 
                                 status = '1' and 
                                 condominio_id = {$condominio_id} 
                                 order by dt_final desc  
                                ");
        
        // pega o último fechamento     
        $i = 1;
        foreach($colunas as $coluna)
        {
            if ( $i <= 1 ) {
                $previsao_arrecadacao = (float)$coluna['previsao_arrecadacao'];
                $taxa_inadimplencia = (float)$coluna['taxa_inadimplencia'];
                $dt_fechamento = (float)$coluna['dt_fechamento'];
                $dt_inicial = $coluna['dt_inicial'];
                $dt_final = $coluna['dt_final'];
                $saldo_inicial = (float)$coluna['saldo_inicial'];
                $receita = (float)$coluna['receita'];
                $despesa = (float)$coluna['despesa'];
                $saldo_final = (float)$coluna['saldo_final'];
                $nota_explicativa = $coluna['nota_explicativa'];
            
            }
            
            $i++; // dados somente do ultimo fechamento
            
        }                        
                               
        // dados das despesas
        // dados do fechamento
        $conn1 = TTransaction::get();
        $colunas1 = $conn1->query("SELECT 
                                sum(contas_receber.valor_pago) as valor, 
                                plano_contas.codigo as classificacao_codigo,     
                                plano_contas.descricao as classificacao_descricao 
                                FROM contas_receber  
                                INNER JOIN plano_contas on contas_receber.classe_id = plano_contas.id 
                                where 
                                contas_receber.condominio_id = '{$condominio_id}' and 
                                contas_receber.situacao = '1' and 
                                (contas_receber.dt_liquidacao >= '{$dt_inicial}' and 
                                contas_receber.dt_liquidacao <= '{$dt_final}')  
                                group by classificacao_descricao, classificacao_codigo
                                ");
        
        // pega o último fechamento     
        $dados = array();
        $dados[] = [ 'Totalização', 'R$' ];

        foreach($colunas1 as $coluna1)
        {
            $dados[] = [ $coluna1['classificacao_descricao'], (float)$coluna1['valor'] ];
            
        }
        
        
        ///////////////////////
       
        $panel = new TPanelGroup('Gráfico Classificação de Receitas');
        $panel->style = 'width: 100%';
        $panel->add($html);
        
        // replace the main section variables
        $html->enableSection('main', array('data'   => json_encode($dados),
                                           'width'  => '100%',
                                           'height'  => '300px',
                                           'title'  => 'Receita',
                                           'ytitle' => 'Accesses', 
                                           'xtitle' => 'Day',
                                           'uniqid' => uniqid()));
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        if ($show_breadcrumb)
        {
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        }
        $container->add($panel);
        parent::add($container);
    }
}

