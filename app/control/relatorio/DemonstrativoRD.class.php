<?php

class DemonstrativoRD extends TPage
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
        $this->form = new BootstrapFormBuilder('form_DemonstrativoRD');
        $this->form->setFormTitle( 'Demonstrativo Receita Despesa Síntetico' );

              
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
                $criteria->add(new TFilter('mostra_fechamento', '=', 'Y'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência {mes_ref}','mes_ref', $criteria);
        
            }else {
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
                $criteria->add(new TFilter('status', '=', '1'));
                $criteria->add(new TFilter('mostra_fechamento', '=', 'Y'));
                //$conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria);
                $fechamento_id = new TDBCombo('fechamento_id', 'facilitasmart', 'Fechamento', 'id', 'Id {id} - Mês de Referência  {mes_ref}','mes_ref', $criteria);
        
            } 
            
        }
        TTransaction::close();
                
        $this->form->addFields( [new TLabel('Fechamento')], [$fechamento_id]);
        





        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Informações Complementares', '<b>'.'Vencimento'.'</b><br>' . '{dt_vencimento}' 
        //. '<br><b>'.'Doc. Pagamento'.'</b><br>' . '{numero_doc_pagamento}'
        //. '<br><b>'.'Documento'.'</b><br>' . '{documento}');
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left', 40);
        $column_classe_id = new TDataGridColumn('classe_id', 'Classe', 'left');
        $column_valor_pago = new TDataGridColumn('valor', 'Valor Pago', 'right');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_classe_id);
        $this->datagrid->addColumn($column_valor_pago);
        
        //$column_classe_id->setTransformer( function($value, $object, $row) {
        //    $classe = new PlanoContas($value);
        //    return $classe->descricao;
        //});
        
        $column_valor_pago->setTransformer( function($value, $object, $row) {
            return 'R$ '.number_format($value, 2, ',', '.');
        });
   
        $action_onEvolucao = new TDataGridAction(array($this, 'onEvolucao'));
        $action_onEvolucao->setButtonClass('btn btn-default btn-sm');
        $action_onEvolucao->setLabel('Evolução');
        $action_onEvolucao->setImage('fa:bar-chart blue');
        //$action_onEvolucao->setField('classe_id');
        $action_onEvolucao->setField('id');
        $action_onEvolucao->setDisplayCondition( array($this, 'displayColumn') );

        $this->datagrid->addAction($action_onEvolucao);

        // create the datagrid model
        $this->datagrid->createModel();






        $table = new TTable;

        parent::add($table);
        
        $panel = new TPanelGroup('Bar chart');
        $panel->style = 'width: 100%';
        
        $this->form->addAction('Gráfico', new TAction(array($this,'onGerar')), 'fa:table blue');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        // add the vbox inside the page
        parent::add($container);
     
    }
    
    /**
     * Define when the action can be displayed
     */
    public function displayColumn( $object )
    {
        if ($object->id == 0)
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Load the datagrid with data
     */
    public function onGerar($param = NULL)
    {
        try
        {
            $string = new StringsUtil;

            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for ContasPagar
            $repository = new TRepository('ContasPagar');
            $limit = 1000;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_liquidacao, mes_ref';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            
            if (!isset($object)) { 
              $objectdatagrid = new stdClass();
            }
            
            /////////// Tratamento dos dados
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $fechamento = new Fechamento($param['fechamento_id']);
            
            //var_dump($param['fechamento_id']);
            //var_dump($fechamento);
            
            $mes_ref = $fechamento->mes_ref;
            $condominio = new Condominio($fechamento->condominio_id);

            $condominio_id = $condominio->id;
            
            $previsao_arrecadacao = $fechamento->previsao_arrecadacao;
            $taxa_inadimplencia = $fechamento->taxa_inadimplencia;
            $dt_fechamento = $fechamento->dt_fechamento;
            $dt_inicial = $fechamento->dt_inicial;
            $dt_final = $fechamento->dt_final;
            $saldo_inicial = $fechamento->saldo_inicial;
            $receita = $fechamento->receita;
            $despesa = $fechamento->despesa;
            $saldo_final = $fechamento->saldo_final;
            $nota_explicativa = $fechamento->nota_explicativa;
          
            $dt_inicial = $fechamento->dt_inicial;
            $dt_final = $fechamento->dt_final;
            
            $conta_fechamento_id = $fechamento->conta_fechamento_id;
            
            $objectdatagrid->id = 0;
            $objectdatagrid->classe_id = "Saldo Anterior";
            $objectdatagrid->valor = $saldo_inicial;
            $this->datagrid->addItem($objectdatagrid);

            /////////////////////////////////////////////////////////////////////////////////////////////////
            // select receitas
            //totalizardor
            $total_receitas = 0;
            $connreceber = TTransaction::get();
            $sqlreceber = "SELECT sum(contas_receber.valor_pago) as recebimentos
                       FROM contas_receber
                        where  
                        contas_receber.condominio_id = " . $condominio_id . " and " .
                        "contas_receber.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_receber.situacao = 1 and 
                        (contas_receber.dt_liquidacao >= '".$dt_inicial."' and contas_receber.dt_liquidacao <= '".$dt_final."')"
                        ;
            $colunasrecebers = $connreceber->query($sqlreceber);

            foreach ($colunasrecebers as $colunareceber)
            {
                $total_receitas = $colunareceber['recebimentos'];
            }
            
           
            // fim totalizador
            
            $connreceber = TTransaction::get();
            $sqlreceber = "SELECT 
       contas_receber.classe_id,
       sum(contas_receber.valor_pago) as valor
FROM contas_receber 
where  
                        contas_receber.condominio_id = " . $condominio_id . " and " .
                        "contas_receber.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_receber.situacao = '1' and 
                        (contas_receber.dt_liquidacao >= '".$dt_inicial."' and contas_receber.dt_liquidacao <= '".$dt_final."')"
                        ;
                        
            $sqlreceber = $sqlreceber . " group by classe_id";
            $colunasreceber = $connreceber->query($sqlreceber);

            foreach ($colunasreceber as $objectrec)
            {
              $conta = new PlanoContas($objectrec['classe_id']);   
              
              TButton::disableField('customform','delete_product'.$i);
              $objectdatagrid->id = $conta->id;
              $objectdatagrid->classe_id = '   ' . $conta->descricao;
              $objectdatagrid->valor = $objectrec['valor'];
              $this->datagrid->addItem($objectdatagrid);

            }
            
            $objectdatagrid->id = 0;
            $objectdatagrid->classe_id = "Total Receitas";
            $objectdatagrid->valor = $total_receitas;
            $this->datagrid->addItem($objectdatagrid);
            
            /// fim select receita /////////////////////////////////////////////////////////////////////////////
            
            // select despesas//////////////////////////////////////////////////////////////////////////////////
        
            //totalizardor
            $total_despesas = 0;
            $conn0 = TTransaction::get();
            $sql0 = "SELECT sum(contas_pagar.valor_pago) as pagamentos
                       FROM contas_pagar 
                        where  
                        contas_pagar.condominio_id = " . $condominio_id . " and " .
                        "contas_pagar.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_pagar.situacao = '1' and 
                        (contas_pagar.dt_liquidacao >= '".$dt_inicial."' and contas_pagar.dt_liquidacao <= '".$dt_final."')"
                        ;
            $colunas0 = $conn0->query($sql0);
            foreach ($colunas0 as $coluna0)
            {
                $total_despesas = $coluna0['pagamentos'];
            }
            
                       
            // fim totalizador
            
            $conn = TTransaction::get();
            $sql = "SELECT 
       contas_pagar.classe_id,
       sum(contas_pagar.valor_pago) as valor
FROM contas_pagar 
where  
                        contas_pagar.condominio_id = " . $condominio_id . " and " .
                        "contas_pagar.conta_fechamento_id = " . $conta_fechamento_id . " and " .
                        "contas_pagar.situacao = '1' and 
                        (contas_pagar.dt_liquidacao >= '".$dt_inicial."' and contas_pagar.dt_liquidacao <= '".$dt_final."')"
                        ;
                        
                       
            $sql = $sql . " group by classe_id";
                        
            $colunaspagar = $conn->query($sql);

            foreach ($colunaspagar as $objectpag)
            {
              $conta = new PlanoContas($objectpag['classe_id']);   

              $objectdatagrid->id = $conta->id;
              $objectdatagrid->classe_id = '   ' . $conta->descricao;
              $objectdatagrid->valor = $objectpag['valor'];
              $this->datagrid->addItem($objectdatagrid);

            }
            
            $objectdatagrid->id = 0;
            $objectdatagrid->classe_id = "Total Despesas";
            $objectdatagrid->valor = $total_despesas;
            $this->datagrid->addItem($objectdatagrid);
            
            /////////////// fim tratamento dos dados

            // resumo
            $objectdatagrid->id = 0;
            $objectdatagrid->classe_id = "Resultado do mês";
            $resultado = $total_receitas-$total_despesas;
            $objectdatagrid->valor = $resultado;
            $this->datagrid->addItem($objectdatagrid);
            
            $objectdatagrid->id = 0;
            $objectdatagrid->classe_id = "Saldo Final";
            $objectdatagrid->valor = $saldo_final;
            $this->datagrid->addItem($objectdatagrid);
            
           
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    function onEvolucao($param = NULL)
    {        
        try
        {
            $html = new THtmlRenderer('app/resources/google_bar_chart.html');
            
            $meses = [ 1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',     4 => 'Abril',    5 => 'Maio',      6 => 'Junho',
                       7 => 'Julho',   8 => 'Agosto',    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro' ];
            
            $data = array();
            $data[] = [ 'Mês', 'Valor' ];
            
            TTransaction::open('facilitasmart');
            $conta = new PlanoContas($param['id']);
            
            if ($conta->tipo == 'D') {
              $grafico_mes = ContasPagar::getDespesaAnaliticaMes( date('Y'), TSession::getValue('id_condominio'), $param['id']);       
            } else {
              $grafico_mes = ContasReceber::getReceitaAnaliticaMes( date('Y'), TSession::getValue('id_condominio'), $param['id']);
            }
            TTransaction::close();

            foreach ($grafico_mes as $mes => $valor)
            {
                $data[] = [ $meses[ (int)$mes], $valor ];
            }
 
            if ($conta->tipo == 'D') {
              $panel = new TPanelGroup('Despesa / mês - ' . date('Y') . ' - ' . $conta->descricao);       
            } else {
              $panel = new TPanelGroup('Receita / mês - ' . date('Y') . ' - ' . $conta->descricao);
            }
         
            $panel->style = 'width:100%';
            $panel->add($html);
            
            if ($conta->tipo == 'D') {
              // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Despesa por mês',
                                               'ytitle' => 'Despesa',
                                               'xtitle' => 'Mês'
                                               ));       
            } else {
              // replace the main section variables
              $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Receita por mês',
                                               'ytitle' => 'Receita',
                                               'xtitle' => 'Mês'
                                               ));
            }
                                                                                    
            //$container = new TVBox;
            //$container->style = 'width: 100%';
            //if ($show_breadcrumb)
            //{
            //    $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            //}
            //$container->add($panel);
            //parent::add($container);
            
            parent::add($panel);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        //parent::add($div);
    }
}
?> 
