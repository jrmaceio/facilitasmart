<?php

/**
 * Description of CurrentState Graphic
 * Evolução de "Cotas do Mês" da Unidade (CONCLUÍDO)
 */
   
class GraficoEvolucaoCotaMes extends TPage
{
    function __construct() 
    { 
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_GraficoEvolucaoCotaMes');
        $this->form->setFormTitle( 'Gráfico Evolução Cotas Mês' );

        // create the form fields
        $data_EmissaoInicio = new TDate('data_EmissaoInicio');
        $data_EmissaoInicio->setMask('dd/mm/yyyy');
        $data_EmissaoInicio->setValue('01/01/'.date('Y'));
        $data_EmissaoFinal = new TDate('data_EmissaoFinal');
        $data_EmissaoFinal->setMask('dd/mm/yyyy');
        $data_EmissaoFinal->setValue(date('d/m/Y'));
        
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
                $criteria->add(new TFilter('id', '=', $user->condominio_id));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);        
                $condominio_id->setValue($user->condominio_id);
                $condominio_id->setEditable(FALSE);
                
                $unidade = new Unidade($user->unidade_id);
                $proprietario = new Pessoa($unidade->proprietario_id);
                
                $criteria = new TCriteria;
                $criteria->add(new TFilter('condominio_id', '=', $user->condominio_id)); 
                $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 'descricao', 'descricao', $criteria);
                                
                $unidade_id->setValue($user->unidade_id);
                $unidade_id->setEditable(FALSE);
        
            }else {
               $criteria = new TCriteria;
               $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); 
               $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 'descricao', 'descricao', $criteria);
                
               $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
               $condominio_id->setValue(TSession::getValue('id_condominio'));
            } 
                       
        }
        
        TTransaction::close();

        $output_type = new TRadioGroup('output_type');

        $unidade_id->setSize('70%');
        $condominio_id->setSize('70%');
         
        // add the fields
        $this->form->addFields( [new TLabel('Data Inicial')], [$data_EmissaoInicio], 
                                [new TLabel('Data Final')], [$data_EmissaoFinal] );
        $this->form->addFields( [new TLabel('Condominio')], [$condominio_id], 
                                [new TLabel('Unidade')], [$unidade_id] );                                

        $btn = $this->form->addAction('Gerar', new TAction(array($this, 'onGenerator')), 'fa:cog blue' );
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';  // para reponsividade $container->style = 'width: 80%'
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);

    }

    function onGenerator($param = NULL)
    {
        $string = new StringsUtil;

        // get the form data into an active record
        $formdata = $this->form->getData();

        $dt_inicial = TDate::date2us($formdata->data_EmissaoInicio);
        $dt_final = TDate::date2us($formdata->data_EmissaoFinal);
        
        $html = new THtmlRenderer('app/resources/google_bar_chart.html');
        
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

        // dados
        $conn1 = TTransaction::get();
        $colunas1 = $conn1->query("select dt_liquidacao, 
        concat(extract(month from dt_vencimento), '/', extract(year from dt_vencimento)) as mes_referencia, 
        coalesce(sum(valor),0) as valor_original, 
        coalesce(sum(valor_pago),0) as valor_pago 
                                   from contas_receber 
                                   where 
                                   contas_receber.condominio_id = {$formdata->condominio_id} and 
                                   (dt_vencimento BETWEEN '{$dt_inicial}' and '{$dt_final}') and  
                                   contas_receber.unidade_id = {$formdata->unidade_id} and 
                                   contas_receber.situacao = '1'   
                                   group by mes_referencia, dt_liquidacao
                                   order by dt_liquidacao
                                ");
        
        /*
        select dt_liquidacao, 
        concat(extract(month from dt_vencimento), '/', extract(year from dt_vencimento)) as mes_referencia, coalesce(sum(valor),0) as valor_original, 
        coalesce(sum(valor_pago),0) as valor_pago 
        from contas_receber 
        where 
        contas_receber.condominio_id = 6 and 
        (dt_vencimento BETWEEN '2017-01-01' AND '2017-12-31') and 
        contas_receber.unidade_id = 303 and 
        contas_receber.situacao = '1' 
        group by mes_referencia, dt_liquidacao 
        order by dt_liquidacao
        */
        
        // pega o último fechamento     
        $dados = array();
        $dados[] = [ 'Data Liquidação', 'R$' ];
        
        foreach($colunas1 as $coluna1)
        {
            $dados[] = [ $string->formatDateBR($coluna1['dt_liquidacao']), (float)$coluna1['valor_pago'] ];
        }
        
        //$dados[] = [ 'Da', 'Value 1', 'Value 2', 'Value 3' ];
        //$dados[] = [ 'Day 1',   120,       140,       160 ];
        //$dados[] = [ 'Day 2',   100,       120,       140 ];
        //$dados[] = [ 'Day 3',   140,       160,       110 ];
        
        
        $div = new TElement('div');
        $div->id    = 'container';
        $div->style = "width:950px;height:600px";
        $div->add($html);
        
        //var_dump($dados);
               
        // replace the main section variables
        $html->enableSection('main', [
                    'data'=> json_encode($dados),
                    'height' => '300px',
                    'precision' => 2,
                    'decimalSeparator' => ',',
                    'thousandSeparator' => '.',
                    'prefix' => 'R$',
                    'sufix' => '',
                    'width' => '100%',
                    'widthType' => '%',
                    'title' => 'Evolução do Pagamentos',
                    'showLegend' => 'true',
                    'showPercentage' => 'false',
                    'barDirection' => 'false'
                ]);
                
               // array('data'   => json_encode($dados),
               //                            'width'  => '100%',
               //                            'height'  => '300px',
               //                            'title'  => 'Evolução do Pagamentos',
               //                            'ytitle' => 'R$', 
               //                            'xtitle' => 'Valores',
               //                            'uniqid' => uniqid()));
                                           
        TTransaction::close();
        
        
        parent::add($div);
        
        // fill the form with data again
        $this->form->setData($formdata); 
    }

}

?>