<?php

class GraficoReceitaDespesa extends TPage
{
    //private $notebook;
    private $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($show_breadcrumb = true)
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Grafico');
        $this->form->setFormTitle( 'Gráfico Receita / Despesa' );

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
            if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', $user->condominio_id));
                $criteria->add(new TFilter('status', '=', '1'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência {mes_ref}','mes_ref', $criteria);
        
            }else {
               $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
                $criteria->add(new TFilter('status', '=', '1'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência  {mes_ref}','mes_ref', $criteria);
        
            } 
            
        }
        TTransaction::close();
                
        $this->form->addFields( [new TLabel('Fechamento')], [$fechamento_id]);
        

        $table = new TTable;

        parent::add($table);
        
        $panel = new TPanelGroup('Bar chart');
        $panel->style = 'width: 100%';
        
        //$this->form->addAction('Gráfico', new TAction(array($this,'onGenerator')), 'fa:table blue');
        $btn_ongenerate = $this->form->addAction('Gerar', new TAction([$this, 'onGenerator']), 'fa:search #ffffff');
        $btn_ongenerate->addStyleClass('btn-primary');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        // add the vbox inside the page
        parent::add($container);
     
    }
    
    function onGenerator($param = NULL)
    {
        $string = new StringsUtil;

        // get the form data into an active record
        $formdata = $this->form->getData();
        
        //$html = new THtmlRenderer('app/resources/google_bar_chart.html'); 
        //$html = new THtmlRenderer('app/resources/google_line_chart.html');
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
 
        $datahoje = date('Y-m-d');
        $partes = explode("-", $datahoje);
        $ano_hoje = $partes[0];
        $mes_hoje = $partes[1];
        $mes_ant  = ((int) $mes_hoje ) -1;
        $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
        $dia_hoje = $partes[2];
                
        $mes_ref = $mes_ant . '/' . $ano_hoje;  
        
        $condominio = new Condominio($condominio_id); 
        
        //parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
        //                TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
        
        if ($formdata->fechamento_id)
        {
                        
            // dados do fechamento
            $conn = TTransaction::get();
            $colunas = $conn->query("SELECT 
                                *
                                FROM fechamento
                                where  
                                id = {$formdata->fechamento_id} and status = '1' 
                                ");
        
            
            //var_dump($colunas);              
            foreach($colunas as $coluna)
            {
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
                               
                                
            $dados = array();
            $dados[] = [ 'Totalização', 'R$' ];
            $dados[] = [ 'Receita', $receita ];
            $dados[] = [ 'Despesa', $despesa ];

            $div = new TElement('div');
            $div->id    = 'container';
            $div->style = "width:950px;height:600px";
            $div->add($html);
        
            //var_dump($dados);
               
            // replace the main section variables
            $html->enableSection('main',  [
                                            'data'   => json_encode($dados),
                                            'height' => '300px',
                                            'precision' => 2,
                                            'decimalSeparator' => ',',
                                            'thousandSeparator' => '.',
                                            'prefix' => 'R$',
                                            'sufix' => '',
                                            'width' => '100%',
                                            'widthType' => '%',
                                            'title'  => 'Comparativo Receitas e Despesas',
                                            'ytitle' => 'R$', 
                                            'xtitle' => 'Valores',
                                            'showLegend' => 'true',
                                            'showPercentage' => 'true',
                                            'barDirection' => 'false']);
        
            parent::add($div);     
            
            // fill the form with data again
            $this->form->setData($formdata);                                                                         
        }
        else
        {
            new TMessage('error', _t('No records found'));
        }
                                   
        TTransaction::close();
        
      
    }
}
?> 
