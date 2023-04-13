<?php
/**
 * VwContasreceberboletoList Listing
 * @author  <your name here>
 */
class ContasReceberBoleto extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
                        
        // creates the form
        $this->form = new TQuickForm('form_search_VwContasreceberboleto');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('VwContasreceberboleto');
        

        // create the form fields
        $mes_ref = new TEntry('mes_ref');
        $cobranca = new TEntry('cobranca');
        $classe_id = new TEntry('classe_id');
        $unidade_id = new TEntry('unidade_id');
        $unidade_nome = new TEntry('unidade_nome');
        $dt_novo_vencimento = new TDate('dt_novo_vencimento');
        //$descricao = new TEntry('descricao');
        $situacao = new TEntry('situacao');
        
        $multa = new TEntry('multa');
        $juros = new TEntry('juros');
        $correcao = new TEntry('correcao');

        $mes_ref->setSize(100);
        $cobranca->setSize(50);
        $unidade_id->setSize(50);
        $unidade_nome->setSize(500);
        $classe_id->setSize(50);
        $situacao->setSize(50);
        $dt_novo_vencimento->setSize(100);

        $multa->setSize(80);
        $juros->setSize(80);
        $correcao->setSize(80);        
        
        $unidade_nome->setEditable(FALSE);
        
        $multa->setNumericMask(2, ',', '.');
        $juros->setNumericMask(3, ',', '.');
        $correcao->setNumericMask(2, ',', '.');

        $lbl_juros = new TLabel('% Juros ao dia');
        $lbl_juros->setFontStyle('b');
        $lbl_correcao = new TLabel('Correção');
        $lbl_correcao->setFontStyle('b');
        $lbl_inad_ate = new TLabel('Novo Vencimento');
        $lbl_inad_ate->setFontStyle('b');
                
        $this->form->addQuickFields('% Multa', array($multa, $lbl_juros, $juros, $lbl_correcao, $correcao, $lbl_inad_ate, $dt_novo_vencimento ));
        
        $lbl_mes_ref = new TLabel('Mês Ref.');
        $lbl_mes_ref->setFontStyle('b');
        
        $this->form->addQuickFields('Cobranca', array($cobranca, $lbl_mes_ref, $mes_ref));
       
        $this->form->addQuickFields('Unidade Id', array($unidade_id, 
        new TLabel('Proprietario'),$unidade_nome));

        // mascaras
        $dt_novo_vencimento->setMask('dd/mm/yyyy');
        
        $dt_novo_vencimento->setValue(Date('d/m/Y'));
        
        // set exit action for input_exit
        $exit_id_unidade = new TAction(array($this, 'onExitIdUnidade'));
        $unidade_id->setExitAction($exit_id_unidade);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('VwContasreceberboleto_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
     
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_receber_mes_ref = new TDataGridColumn('receber_mes_ref', 'Mês Ref.', 'left');
        $column_receber_valor_boleto = new TDataGridColumn('receber_valor_boleto', 'Valor Boleto', 'left');
        //$column_receber_imovel_id = new TDataGridColumn('receber_imovel_id', 'Receber Imovel Id', 'right');
        $column_receber_dt_vencimento = new TDataGridColumn('receber_dt_vencimento', 'Vencimento', 'left');
        $column_receber_dt_lancamento = new TDataGridColumn('receber_dt_lancamento', 'Lançamento', 'left');
        $column_receber_cobranca = new TDataGridColumn('receber_cobranca', 'Cobrança', 'left');
        $column_unidade_id = new TDataGridColumn('unidade_id', 'Unidade Id', 'right');
        $column_unidade_descricao = new TDataGridColumn('unidade_descricao', 'Unidade', 'left');
        $column_pes_nome = new TDataGridColumn('pes_nome', 'Proprietário', 'left');
        
        $column_imovel_resumo = new TDataGridColumn('imovel_resumo', 'Resumo', 'left');
        //$column_imovel_nome = new TDataGridColumn('imovel_nome', 'Imovel Nome', 'left');
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_receber_mes_ref);
        $this->datagrid->addColumn($column_receber_valor_boleto);
        //$this->datagrid->addColumn($column_receber_imovel_id);
        $this->datagrid->addColumn($column_receber_dt_vencimento);
        $this->datagrid->addColumn($column_receber_dt_lancamento);
        $this->datagrid->addColumn($column_receber_cobranca);
        $this->datagrid->addColumn($column_unidade_id);
        $this->datagrid->addColumn($column_unidade_descricao);
        $this->datagrid->addColumn($column_pes_nome);
        
        $this->datagrid->addColumn($column_imovel_resumo);
       // $this->datagrid->addColumn($column_imovel_nome);
         
         // creates the datagrid actions
        $action1 = new TDataGridAction(array($this, 'onBoleto'));
        //$action1 = new TDataGridAction(array('Boleto', 'onGenerate'));
        $action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        $action1->setImage('fa:barcode green');
        $action1->setField('receber_mes_ref');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
 
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('2a via de boleto', $this->form));
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
   
    function onBoleto($param)
    {
      var_dump($this->form->getData());
      var_dump($param);
      //$data = $this->form->getData();
      //var_dump($data); 
      //TApplication::loadPage('Boleto', 'onGenerate');
      //TApplication::loadPage('Boleto', 'onGenerate', (array) $data);
      //TApplication::loadPage('BoletoView', 'onGenerate', (array) $data);
      
      return;
              // define the delete action
        $action = new TAction(array($this, 'Boleto'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Emitir Boleto', $action);
    }
    
    function Boleto($param)
    {
              //var_dump($param);
        
        //$data = $this->form->getData();
        //var_dump($data);
        
        $dadosboleto = $param;
        
        $this->string = new StringsUtil;
        
       
        //try
        //{
          TTransaction::open('facilitasmart');
          
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
    
    public static function onExitIdUnidade($param)
    {
        try
        {
            TTransaction::open('facilita');
            $unidade_desc = Unidades::RetornaDescricaoUnidade($param['unidade_id']);
            $unidade_prop_nome = Unidades::RetornaProprietarioUnidade($param['unidade_id']);
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        $obj = new StdClass;
        //$obj->unidade_descricao = $unidade_desc;
        $obj->unidade_nome = $unidade_desc . ' - ' . $unidade_prop_nome;
        TForm::sendData('form_search_VwContasreceberboleto', $obj);
        
        
        //new TMessage('info', 'Message on field exit. <br>You have typed: ' . $param['input_exit']);
    }
    
    
   
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('VwContasreceberboletoList_filter_receber_mes_ref',   NULL);
        TSession::setValue('VwContasreceberboletoList_filter_receber_cobranca',   NULL);
        TSession::setValue('VwContasreceberboletoList_filter_unidade_id',   NULL);
         
        // filtra pelo imovel escolhido em mes_ref imoveis
        $imovel_id = TSession::getValue('id_imovel');
        $filter = new TFilter('receber_imovel_id', '=', TSession::getValue('id_imovel')); // create the filter
        TSession::setValue('VwContasreceberboletoList_filter_receber_imovel_id',   $filter); // stores the filter in the session
        
        if (isset($data->receber_mes_ref) AND ($data->receber_mes_ref)) {
            $filter = new TFilter('receber_mes_ref', '=', "{$data->receber_mes_ref}"); // create the filter
            TSession::setValue('VwContasreceberboletoList_filter_receber_mes_ref',   $filter); // stores the filter in the session
        }

        if (isset($data->receber_cobranca) AND ($data->receber_cobranca)) {
            $filter = new TFilter('receber_cobranca', '=', "{$data->receber_cobranca}"); // create the filter
            TSession::setValue('VwContasreceberboletoList_filter_receber_cobranca',   $filter); // stores the filter in the session
        }

        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "{$data->unidade_id}"); // create the filter
            TSession::setValue('VwContasreceberboletoList_filter_unidade_id',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('VwContasreceberboleto_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for VwContasreceberboleto
            $repository = new TRepository('VwContasreceberboleto');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'unidade_descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('VwContasreceberboletoList_filter_receber_mes_ref')) {
                $criteria->add(TSession::getValue('VwContasreceberboletoList_filter_receber_mes_ref')); // add the session filter
            }else {
              // se ainda nao definido, força pelo já selecionado no inicio
              $mes_referencia = TSession::getValue('mesref');
        
              $filter = new TFilter('receber_mes_ref', '=', "{$mes_referencia}"); // create the filter
              TSession::setValue('VwContasreceberboletoList_filter_receber_mes_ref',   $filter); // stores the filter in the session
              $criteria->add(TSession::getValue('VwContasreceberboletoList_filter_receber_mes_ref')); // add the session filter
            }

            if (TSession::getValue('VwContasreceberboletoList_filter_receber_imovel_id')) {
                $criteria->add(TSession::getValue('VwContasreceberboletoList_filter_receber_imovel_id')); // add the session filter
            }

            if (TSession::getValue('VwContasreceberboletoList_filter_receber_cobranca')) {
                $criteria->add(TSession::getValue('VwContasreceberboletoList_filter_receber_cobranca')); // add the session filter
            }

            if (TSession::getValue('VwContasreceberboletoList_filter_unidade_id')) {
                $criteria->add(TSession::getValue('VwContasreceberboletoList_filter_unidade_id')); // add the session filter
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
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
    
   
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
