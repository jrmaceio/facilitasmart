<?php
/**
 * contas_receberForm Registration
 * @author  <your name here>
 *
 * Controle de baixa :
 * Campo Situacao =======> 0 - Emitida
 *                         1 - Baixada
 *                         2 - Em acordo
 *                         3 - Sub Júdice
 *
 *
 *
 *
 */
class ContasReceberForm extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $notebook;
    
    private $string;
    
    /**
     * Class constructor
     * Creates the page and the registration form
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
        $this->form = new BootstrapFormBuilder('form_contas_receber');
        $this->form->setFormTitle('Lançamento individual de contas a receber');
        
        // create the form fields
        $id                             = new TEntry('id');
        
        $criteria = new TCriteria;
        //$criteria->add(new TFilter('id', '=', $user->condominio_id));
        //$criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio')));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
        
        $mes_ref                        = new TEntry('mes_ref');
        
        //$cobranca                       = new TEntry('cobranca');
        //$cobranca->setValue('1'); 
       
        $cobranca = new TCombo('cobranca');
        $cobranca->addItems(array(      1=>'1',
                                        2=>'2',
                                        3=>'3',
                                        4=>'4',
                                        5=>'5',
                                        6=>'6',
                                        7=>'7',
                                        7=>'8',
                                        7=>'9',
                                        7=>'10'));
                                        
        $cobranca->addValidation('Cobrança', new TRequiredValidator ); 
                
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $tipo_lancamento->setValue('M'); // M - Manual A - Automatico I - Interno 
        //$tipo_lancamento->setEditable(FALSE); 
        
        /////$classe_chave = new TEntry('classe_chave');
        //$/classe_chave->setEditable(FALSE); 
              
        //$classe_id                      = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);
        //TDBCombo('proprietario_id', 'atividade', 'Pessoa', 'pessoa_codigo', 'pessoa_nome', 'pessoa_nome', $criteria);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
        
        $nome_responsavel = new TEntry('nome_responsavel');
        $nome_responsavel->setValue('O MESMO'); 

        //$unidade_nome_prop = new TEntry('unidade_nome_prop');
        //$unidade_nome_prop->setEditable(FALSE);
        
        // set exit action for input_exit
        //$exit_id_unidade = new TAction(array($this, 'onExitIdUnidade'));
        //$unidade_id->setExitAction($exit_id_unidade);
                
        $change_mes_ref = new TAction(array($this, 'onTestaMesRef'));
        $mes_ref->setExitAction($change_mes_ref);
        //$mes_ref->setExitAction(new TAction(array($this, 'onTestaMesRef')));
        
        //$unidade_descricao = new TEntry('unidade_descricao');
        //$unidade_descricao->setEditable(FALSE);
        
        //$dt_lancamento                  = new TDate('dt_lancamento');
        $dt_lancamento   = new TEntry('dt_lancamento');
        $dt_lancamento->setEditable(FALSE);        
        $dt_lancamento->setMask('dd/mm/yyyy');        
        $dt_lancamento->setValue(date('d/m/Y')); 

       
        $dt_vencimento                  = new TDate('dt_vencimento');
        //$dt_vencimento   = new TEntry('dt_vencimento');
        $dt_vencimento->setMask('dd/mm/yyyy'); 
        
        $valor                          = new TEntry('valor');
        $valor->setNumericMask(2, ',', '.');
        
        $descricao                      = new TEntry('descricao');
        
        // não pegava a situacao porque não estava definido aqui
        $situacao = new THidden('situacao');
        
        $arquivo_retorno = new TEntry('arquivo_retorno');
        
        
        $dt_pagamento = new TDate('dt_pagamento');
        $dt_pagamento->setMask('dd/mm/yyyy');

        $dt_liquidacao = new TDate('dt_liquidacao');
        $dt_liquidacao->setMask('dd/mm/yyyy');

        // popover em campo        
        $dt_liquidacao->popover = 'true';
        $dt_liquidacao->popside = 'top';
        $dt_liquidacao->poptitle = 'Data Liquidação';
        $dt_liquidacao->popcontent = 'Data que houve o <i>crédito</i> na conta.';

        $multa = new TEntry('multa');
        $multa->setNumericMask(2, ',', '.');
        $juros = new TEntry('juros');
        $juros->setNumericMask(2, ',', '.');  
        $correcao = new TEntry('correcao');
        $correcao->setNumericMask(2, ',', '.');
        $desconto = new TEntry('desconto');
        $desconto->setNumericMask(2, ',', '.');  
        $valor_pago = new TEntry('valor_pago');
        $valor_pago->setNumericMask(2, ',', '.'); 

        $tarifa = new TEntry('tarifa');
        $tarifa->setNumericMask(2, ',', '.'); 
        $valor_creditado = new TEntry('valor_creditado');
        $valor_creditado->setNumericMask(2, ',', '.'); 

        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        //$button_responsavel = new TButton('button_responsavel');
        
        $arquivo_retorno->setEditable(FALSE);
        
        // define the sizes
        $id->setSize('50%');
        $condominio_id->setSize('100%');
        $mes_ref->setSize('50%');
        $cobranca->setSize('100%');
        $tipo_lancamento->setSize('50%');
        $classe_id->setSize('100%');
        $unidade_id->setSize('100%');
        $dt_lancamento->setSize('100%');
        $dt_vencimento->setSize('100%');
        $valor->setSize('100%');
        $descricao->setSize('100%');
        $nome_responsavel->setSize('100%');
        //$unidade_nome_prop->setSize('100%');

        $dt_pagamento->setSize('100%');
        $dt_liquidacao->setSize('100%');
        $multa->setSize('100%');
        $juros->setSize('100%');
        $correcao->setSize('100%');
        $desconto->setSize('100%');
        $valor_pago->setSize('100%');
        $tarifa->setSize('100%');
        $valor_creditado->setSize('100%');

        // validations
        $classe_id->addValidation('Classe', new TRequiredValidator);
        // aceita vazio em Movimentação Bancária ===== $unidade_id->addValidation('unidade_id', new TRequiredValidator);
        $dt_lancamento->addValidation('Data de Lançamento', new TRequiredValidator);
        $dt_vencimento->addValidation('Data de Vencimento', new TRequiredValidator);
        $valor->addValidation('Valor', new TRequiredValidator);
        //$nome_responsavel->addValidation('Nome do Responsável', new TRequiredValidator);

        $this->form->appendPage('Lançamento');
        $this->form->addFields([new TLabel('Id')], [$id], [new TLabel('Condominio')], [$condominio_id]);
        $this->form->addFields([new TLabel('Mês Referência')], [$mes_ref], [new TLabel('Cobranca')], [$cobranca]);
        $this->form->addFields([new TLabel('Tipo Lançamento')], [$tipo_lancamento], [new TLabel('B = Movimentação Bancária')]);
        
        //$button_responsavel->setAction(new TAction([$this, 'onResponsavel']), 'Responsável');
        //$button_responsavel->addStyleClass('btn-success');
        //$button_responsavel->setImage('fa:address-book-o #ffffff');
        //$button_responsavel->style = 'color: white';
        
        $this->form->addFields([new TLabel('Unidade')], [$unidade_id]);
        
        //$this->form->addFields([new TLabel('Vencimento:')], [$dt_vencimento],[new TLabel('Responsável')], [$nome_responsavel,$button_responsavel]); 
        $this->form->addFields([new TLabel('Vencimento:')], [$dt_vencimento],[new TLabel('Responsável')], [$nome_responsavel]); 

        $this->form->addFields([new TLabel('Valor')], [$valor], [new TLabel('Classe')], [$classe_id]);
        
        $this->form->addFields([new TLabel('Descricao')], [$descricao]);
        
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id], [new TLabel('Dt Lancamento')], [$dt_lancamento]);
       
        $conta_fechamento_id->setValue( TSession::getValue('conta_fechamento') );
       
        //$nome_responsavel->setValue( TSession::getValue('nome_responsavel') );
            
        $mes_ref->setValue( TSession::getValue('mesref') );
        
        $condominio_id->setValue( TSession::getValue('condominio_id') );
        $cobranca->setValue( TSession::getValue('cobranca') );
        
        $this->form->appendPage('Pagamento/Baixa');
        $this->form->addFields( [new TLabel('Data Pagamento:')], [$dt_pagamento],
                                 [new TLabel('Data Liquidação:')], [$dt_liquidacao] );  
        $this->form->addFields( [new TLabel('Multa:')], [$multa],
                                 [new TLabel('Juros:')], [$juros] );
        $this->form->addFields( [new TLabel('Correção:')], [$correcao],
                                 [new TLabel('Desconto:')], [$desconto] );     
       
        $this->form->addFields( [new TLabel('Valor Pago:')], [$valor_pago], [new TLabel('Valor Creditado:')], [$valor_creditado] ); 
        $this->form->addFields( [new TLabel('Tarifa:')], [$tarifa], [new TLabel('Arquivo Retorno:')], [$arquivo_retorno]); 
       
        $this->form->addFields( [new TLabel('')], [$situacao] );  
        
        $change_data_pagamento = new TAction(array($this, 'onChangeDataPagamento'));
        $dt_pagamento->setExitAction($change_data_pagamento);
        
        $change_data_liquidacao = new TAction(array($this, 'onChangeDataLiquidacao'));
        $dt_liquidacao->setExitAction($change_data_liquidacao);
        
        $change_valor = new TAction(array ($this, 'onCalculaValorPagamento'));
        $valor_pago->setExitAction($change_valor);
        
        //$change_unidade_id = new TAction(array ($this, 'onResponsavel'));
        //$dt_vencimento->setExitAction($change_unidade_id);
        
        ///////$dt_vencimento->setExitAction(new TAction(array($this, 'onResponsavel')));
        
        $multa->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $juros->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $correcao->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $desconto->setExitAction(new TAction(array($this, 'onUpdateTotal')));

        $logado_id = new THidden('logado_id');
        $logado_id->setValue(TSession::getValue('userid'));
        
        $this->form->appendPage('Boleto');
        
        $nosso_numero = new TEntry('nosso_numero');
        $multa_boleto_cobranca = new TEntry('multa_boleto_cobranca');
        $juros_boleto_cobranca = new TEntry('juros_boleto_cobranca');
        $desconto_boleto_cobranca = new TEntry('desconto_boleto_cobranca');
        $dt_limite_desconto_boleto_cobranca = new TDate('dt_limite_desconto_boleto_cobranca'); 
        $documento_boleto_cobranca = new TEntry('documento_boleto_cobranca');
        $remessa = new TEntry('remessa');

        $dt_limite_desconto_boleto_cobranca->setMask('dd/mm/yyyy');

        $remessa->setEditable(FALSE);

        $nosso_numero->setSize('50%');
        $multa_boleto_cobranca->setSize('50%');
        $juros_boleto_cobranca->setSize('50%');
        $desconto_boleto_cobranca->setSize('50%');
        $dt_limite_desconto_boleto_cobranca->setSize('50%');
        $documento_boleto_cobranca->setSize('50%');
        $remessa->setSize('50%');

        $multa_boleto_cobranca->setNumericMask(2, ',', '.');
        $juros_boleto_cobranca->setNumericMask(2, ',', '.');    
        $desconto_boleto_cobranca->setNumericMask(2, ',', '.');  
        
        $this->form->addFields( [new TLabel('Nosso Número:')], [$nosso_numero] );
        $this->form->addFields( [new TLabel('Multa:')], [$multa_boleto_cobranca] );
        $this->form->addFields( [new TLabel('Juros ao mês:')], [$juros_boleto_cobranca] );
        $this->form->addFields( [new TLabel('Desconto:')], [$desconto_boleto_cobranca] );  
        $this->form->addFields( [new TLabel('Dt Limite Desconto:')], [$dt_limite_desconto_boleto_cobranca] );  
        $this->form->addFields( [new TLabel('Documento Boleto Cobrança:')], [$documento_boleto_cobranca] ); 
        $this->form->addFields( [new TLabel('Número da Remessa:')], [$remessa] ); 
        
        if (empty($id))
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('condominio_id'));
            $multa_boleto_cobranca->setValue(number_format($condominio->multa, 2, ',', '.'));
            $juros_boleto_cobranca->setValue(number_format($condominio->juros), 2, ',', '.');
            $desconto_boleto_cobranca->setValue(number_format($condominio->desconto, 2, ',', '.'));
            TTransaction::close();


        }
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
            $nome_responsavel->setEditable(FALSE);
        } 
        
        // inicio aba split
        $this->form->appendPage('Split');
         
        //$split_active = new TEntry('split_active');
        $split_active = new TCombo('split_active');
        $split_active->addItems(array(  'Y'=>'Ativado',
                                        'N'=>'Desativado'));
                                        
        $split_active->addValidation('Split', new TRequiredValidator ); 
        
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("administradora", "=", 'Y'));
        $split_administradora_id = new TDBCombo('split_administradora_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
        //$split_administradora_id = new TEntry('split_administradora_id');
        
        $split_valor_fixo = new TEntry('split_valor_fixo');
        //$split_valor_porcentagem = new TEntry('split_valor_porcentagem');
        //$split_porcentagem_encargos = new TDate('split_porcentagem_encargos'); 
        //$split_porcentagem_multa = new TEntry('split_porcentagem_multa');
        //$split_porcentagem_juros = new TEntry('split_porcentagem_juros');
        //$split_porcentagem_desconto = new TEntry('split_porcentagem_desconto');
        
        $split_valor_fixo->setNumericMask(2, ',', '.');
               
        
        $this->form->addFields( [new TLabel('Ativar Split (Y/N)')], [$split_active] );
        $this->form->addFields( [new TLabel('Administradora:')], [$split_administradora_id] );
        $this->form->addFields( [new TLabel('Valor do Split:')], [$split_valor_fixo] );   
        
        // fim aba split
        
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'), new TAction(array($this, 'onEdit')), 'bs:plus-sign green');
        //$this->form->addAction( 'Atualizar Nome Responsável', new TAction(array($this, 'onNomeResp')), 'bs:plus-sign green');
        
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
        
    }
    
    function onNomeResp() //atualizar o campo nome do responsavel que foi criado depois em uma update
    {
        try {
            
            return;      
            
            
            
            
            TTransaction::open('facilitasmart'); //abre transação
            //$objCtsReceber = ContasReceber::all(); // carrega todos os dados da tabela para atualizar
            
            $repository = new TRepository('ContasReceber');
            $criteria = new TCriteria;
            $filter1 = new TFilter('condominio_id', '=', '11');// erro no 11
            $filter2 = new TFilter('situacao', '=', '0');
            $criteria->add($filter1);
            $criteria->add($filter2);
            $objects = $repository->load($criteria, FALSE);
            
            foreach ($objects as $object)
            {
                $unidade = new Unidade($object->unidade_id);
                $pessoa = new Pessoa($unidade->proprietario_id);
                
                //if ($object->nome_responsavel == 'nome_responsavel') {
                
                    if ($pessoa->nome== null and $object->tipo_lancamento == 'B') {
                        $pessoa->nome = 'lançamento bancário';
                    }
                    
                    $object->nome_responsavel = $pessoa->nome;
                    $object->store();
                //}
                
            } 

            TTransaction::close(); //fecha transação
            new TMessage('warning', 'Atualização concluída !');
            

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
     } 
    
    public static function onResponsavel($param)
    {
        $obj = new StdClass;
        
        //var_dump($param);
        //new TMessage('info', 'ENTROU !');
        
        if(isset($param['nome_responsavel']))
        {    
            new TMessage('warning', 'Confirme o nome do responsável !');
            
            TTransaction::open('facilitasmart');
            $unidade = new Unidade($param['unidade_id']);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $obj->nome_responsavel = $pessoa->nome;
            TTransaction::close(); 

        } else {
            TTransaction::open('facilitasmart');
            $unidade = new Unidade($param['unidade_id']);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $obj->nome_responsavel = $pessoa->nome;
            TTransaction::close(); 
            
        }
           
        TForm::sendData('form_contas_receber', $obj, FALSE, FALSE);
        
    }
    
    
    public static function onCalculaValorPagamento($param)
    {
        $obj = new StdClass;
        $string = new StringsUtil;
        
        $obj->data_pagamento  = $param['dt_pagamento'];
        $obj->data_liquidacao = $param['dt_liquidacao'];
        $obj->valor_pagamento = $param['valor_pago'];
        $obj->valor           = $param['valor'];
        
        //var_dump($param);
        if(isset($param['valor_pago']))
        {
            //var_dump($param['valor_pago']);
            if($string->desconverteReais($param['valor_pago']) < $string->desconverteReais($param['valor']))
            {
                $calculo_teste = (
                $string->desconverteReais($param['valor']) + 
                $string->desconverteReais($param['multa']) + 
                $string->desconverteReais($param['juros']) + 
                $string->desconverteReais($param['correcao']) )
                - $string->desconverteReais($param['desconto']);
                
                //var_dump($calculo_teste);
                if ($string->desconverteReais($param['valor_pago']) != $calculo_teste ) {
                  new TMessage('warning', 'Divergencia entre o valor pago e valor original !');
                  //$obj->valor_pago = '';
                }
            }
            else
            {
                //$horas = $param['orcamento_horas'];
                //$valor = $string->desconverteReais($valor_total) + $string->desconverteReais($param['valor_pagamento']);
                //$saldo = $string->desconverteReais($param['valor_total']) - $valor;
                //$obj->valor_total_parcial = number_format($valor, 2, ',', '.');
                //$obj->valor_saldo         = number_format($saldo, 2, ',', '.');
            }
           
            TForm::sendData('form_contas_receber', $obj, FALSE, FALSE);
            
        }
        
    }
    
    public static function onTestaMesRef($param)
    {
        $obj = new StdClass;
        $string = new StringsUtil;
        
            
        if(isset($param['mes_ref']))
        {
            // valida o mes referencia
            $mesreferencia = explode('/', $param['mes_ref']);
            
            $d = '01';
	        $m = $mesreferencia[0];
	        $y = isset($mesreferencia[1]) ? $mesreferencia[1] : 0;
	        
	        //if (isset($mesreferencia[1]))
	        //{
	        //    $y = $mesreferencia[1];
            //
            //}
            
	        // verifica se a data é válida!  // 1 = true (válida)  // 0 = false (inválida)
	        $res = checkdate($m,$d,$y);
	        
	        if ($res == 1){
	          if ( strlen($y) == 4){
              } else {
                  new TMessage('warning', 'Mês Referência Inválido');
	               $obj->mes_ref='';
              }
	        } else {
	           //echo "data inválida!";
	           new TMessage('warning', 'Mês Referência Inválido');
	           $obj->mes_ref='';
	       
	        }    
            TForm::sendData('form_contas_receber', $obj, FALSE, FALSE);
        }
    }
    
    public static function onChangeDataPagamento($param)
    {
         
        $obj = new StdClass;
        $string = new StringsUtil;
        
        $hoje                = date('d/m/Y');
        $obj->data_pagamento = $param['dt_pagamento'];
        
        if(strtotime($string->formatDate($obj->data_pagamento)) > strtotime($string->formatDate($hoje)))
        {
    	    new TMessage('error', 'Data pagamento maior que data atual');
        }
        
        $obj->dt_liquidacao = $obj->data_pagamento;
        
        TForm::sendData('form_contas_receber', $obj, FALSE, FALSE);
       
    }
    
    public static function onChangeDataLiquidacao($param)
    {
         
        $obj = new StdClass;
        $string = new StringsUtil;
        
        $hoje                 = date('d/m/Y');
        $obj->data_liquidacao = $param['dt_liquidacao'];
        
        if(strtotime($string->formatDate($obj->data_liquidacao)) > strtotime($string->formatDate($hoje)))
        {
 	        new TMessage('error', 'Data liquidacao maior que data atual');
        }
        
        TForm::sendData('form_contas_receber', $obj, FALSE, FALSE);
       
    }
    
     /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        $string = new StringsUtil;
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            //setar log para teste
            //TTransaction::setLogger(new TLoggerTXT('/var/www/html/facilitasmart/log.txt')); 
            //TTransaction::log("** inserting contas receber"); 
            
            // get the form data into an active record contas_receber
            $object = $this->form->getData('ContasReceber');
            
            // teste se não existe o mesmo lancamento gravado
            $conn = TTransaction::get();
        
            $result = $conn->query("select *
                                    from contas_receber where 
                                    unidade_id = '{$object->unidade_id}' and 
                                     classe_id = '{$object->classe_id}' and
                                       mes_ref = '{$object->mes_ref}' and
                                      cobranca = '{$object->cobranca}'
                                   ");
            $resultado = ''; // evita erro de variavel inexistente em caso que o sql é vazio
            
            foreach ($result as $row)
            {
              $resultado = $row['id'];
            }
            
            //var_dump($resultado);
            //if ($resultado == null) {
            //  new TMessage('info', 'Problema com a unidade !');
            //  TTransaction::close(); 
            //  $this->form->setData($object); // mantem os dados digitados;
            //  return;
            //}
            
            if ($resultado != '' and empty($object->id) and $object->tipo_lancamento != 'B') {
              new TMessage('info', 'Já existe um lançamento com estes dados id=' . $resultado. ' !');
              TTransaction::close(); 
              $this->form->setData($object); // mantem os dados digitados;
              return;
            }
            
            
            if ( $object->situacao == '1') {
                new TMessage('info', 'Título já baixado, a alteração não permitida !');
                return;
            }
            
            if ( $object->situacao == '2') {
                new TMessage('info', 'Título em acordo, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            if ( $object->situacao == '3') {
                new TMessage('info', 'Título em sub-júdice, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            // teste se a unidade é mesmo desse imovel
            $unidade = new Unidade($object->unidade_id);
            
            if ( $object->condominio_id != $unidade->condominio_id ) {
                if ( $object->tipo_lancamento != 'B' ) { // movimentacao bancária
                    new TMessage('info', 'A unidade não pertence ao condomínio escolhido, inclusão cancelada !');
                    TTransaction::close(); 
                    $this->form->setData($object); // mantem os dados digitados;
                    return;
                }
            }
            
            // valida a data de vencimento e mes_ref
            $dt_venc = explode("/", $object->dt_vencimento);
            $mes_referencia = explode("/", $object->mes_ref);

            if ( $dt_venc[1] != $mes_referencia[0]) {
                //new TMessage('info', 'Divergência entre o mês da data de vencimento o Mês Ref. !');
                //TTransaction::close(); 
                //$this->form->setData($object); // mantem os dados digitados;
                //return; 
            } 
            
            if ( $dt_venc[2] != $mes_referencia[1]) {
                //new TMessage('info', 'Divergência entre o ano da data de vencimento o Mês Ref !');
                //TTransaction::close(); 
                //$this->form->setData($object); // mantem os dados digitados;
                //return; 
            }       
            
            if (!$object->conta_fechamento_id) 
            {
              new TMessage('error', '<b>Error</b> ' . 'Preencha a conta de recebimento !'); // shows the exception error message
              return;
            }
                   
            // verifica sem é uma baixa manual, se sim, não verifica pela data de vencimento e sim pela data de pagamento
            if ($object->valor_pago > 0 ) {
            
              $dt_pag = explode("/", $object->dt_pagamento);
              $mes_referencia = $dt_pag[1].'/'.$dt_pag[2];
              //var_dump($mes_referencia);
              //var_dump($object->condominio_id);    
              // verifica se existe fechamento aberto possivel de edicao
              $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $mes_referencia, $object->conta_fechamento_id);
              
              //status 0 -> fechamento aberto possibilidade de fazer alterações
              // status 1 -> fechado, nao altera nada
              if ( $status == 1 ) {
                new TMessage('info', 'Não existe um Fechamento aberto para a data de pagamento !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return;    
              }
                  
            }else {
                $dt_pag = explode("/", $object->dt_vencimento);
                $mes_referencia = $dt_pag[1].'/'.$dt_pag[2];
                
                // verifica se existe fechamento aberto possivel de edicao
                $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $mes_referencia, $object->conta_fechamento_id);
              
                //status 0 -> fechamento aberto possibilidade de fazer alterações
                // status 1 -> fechado, nao altera nada
                if ( $status == 1 ) {
                  new TMessage('info', 'Não existe um Fechamento aberto para a data de vencimento !');
                  TTransaction::close(); 
                  $this->form->setData($object); // mantem os dados digitados;
                  return;    
                }
            }
        
            // alteração    
            if (!empty($object->id))
            {
                $dt_venc = explode("/", $object->dt_vencimento);
                $mes_referencia = $dt_venc[1].'/'.$dt_venc[2];
              
                $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $mes_referencia, $object->conta_fechamento_id);
            
                //var_dump($status);
                
                ////status 1 -> fechado, nao altera nada
                if ( $status == 1 ) {
                    new TMessage('info', 'Não existe um Fechamento aberto para a data de vencimento !');
                    TTransaction::close(); 
                    $this->form->setData($object); // mantem os dados digitados;
                    return;    
                }
                
            }
        
            // NAO PRECISA VARIFICAR, PORQUE POSSO PAGAR UM VENCIMENTO ANTIGO EM UM MES NA FRENTE                  
            // verifica se existe fechamento aberto para a data de vencimento - possivel de edicao
            //$dt_venc = explode("/", $object->dt_vencimento);
           // $mes_referencia = $dt_venc[1].'/'.$dt_venc[2];
              
           // $status = ContasReceber::retornaStatusFechamento($object->condominio_id, $mes_referencia);
            
            ////status 1 -> fechado, nao altera nada
            //if ( $status == 1 ) {
            //    new TMessage('info', 'Não existe um Fechamento aberto para a data de vencimento !');
            //    TTransaction::close(); 
            //    $this->form->setData($object); // mantem os dados digitados;
            //    return;    
            //  }
                  
            // MAIUSCULO
            $object->tipo_lancamento = strtoupper($object->tipo_lancamento);
            
            //formato necessário no mysql
            $object->dt_lancamento = TDate::date2us($object->dt_lancamento ); 
            $object->dt_vencimento = TDate::date2us($object->dt_vencimento );
            $object->dt_pagamento = TDate::date2us($object->dt_pagamento );
            $object->dt_liquidacao = TDate::date2us($object->dt_liquidacao );
            $object->dt_limite_desconto_boleto_cobranca = TDate::date2us($object->dt_limite_desconto_boleto_cobranca);
            
            // atribui valores
            $object->dt_ultima_alteracao = date('Y-m-d H:i');
            $object->usuario_id = $object->logado_id;  
                          
            $object->valor ? $object->valor = $string->desconverteReais($object->valor) : null;
            $object->multa ? $object->multa = $string->desconverteReais($object->multa) : null;
            $object->juros ? $object->juros = $string->desconverteReais($object->juros) : null;
            $object->correcao ? $object->correcao = $string->desconverteReais($object->correcao) : null;
            $object->desconto ? $object->desconto = $string->desconverteReais($object->desconto) : null;
            $object->valor_pago ? $object->valor_pago = $string->desconverteReais($object->valor_pago) : null;

            $object->tarifa ? $object->tarifa = $string->desconverteReais($object->tarifa) : null;
            $object->valor_creditado ? $object->valor_creditado = $string->desconverteReais($object->valor_creditado) : null;
            
            $object->multa_boleto_cobranca ? $object->multa_boleto_cobranca = $string->desconverteReais($object->multa_boleto_cobranca) : null;
            $object->juros_boleto_cobranca ? $object->juros_boleto_cobranca = $string->desconverteReais($object->juros_boleto_cobranca) : null;
            $object->desconto_boleto_cobranca ? $object->desconto_boleto_cobranca = $string->desconverteReais($object->desconto_boleto_cobranca) : null;
            
            $object->split_valor_fixo ? $object->split_valor_fixo = $string->desconverteReais($object->split_valor_fixo) : null;
                     
            $grava_num_doc = false;

            if (empty($object->id))
            {
                $grava_num_doc = true;

            }

            if ( $object->tipo_lancamento == 'B' ) { // Movimentação bancária fica como unidade 0
                $object->unidade_id = 0;
                
            }
                        
            $this->form->validate(); // form validation
            
            // valida a data de vencimento e mes_ref
            $dt_venc = explode("/", $object->dt_vencimento);
            $mes_referencia = explode("/", $object->mes_ref);
           
            if(isset($object->valor_pago))
            {
                if ( $object->valor_pago > 0 and $object->valor_creditado > 0 )
                {
                  $object->situacao = '1'; // baixado
                  $object->conta_fechamento_id = $object->conta_fechamento_id;
                }
            }
            
            // se lancamento novo
            if (empty($object->id))
            {
                $object->situacao = '0'; // emitido
                
            }
            
            // se alteracao grava log
            if (!empty($object->id))
            {
                //criando log 
                //TTransaction::setLogger(new TLoggerTXT('log/contas_receberUp'.$object->id.'('.date('Y-m-d H:i:s').')'.'.txt')); 
                //$user = TSession::getValue('login');
                //TTransaction::Log($user . ' ' . ' - Alteraçao de título por usuário ');
                
            }
            
            //var_dump($object);
            
            $object->store(); // stores the object

            if ( $grava_num_doc ) {
                $object->documento_boleto_cobranca = $object->id;
                $object->store();
            }
    
            $object->split_valor_fixo ? $object->split_valor_fixo = number_format($object->split_valor_fixo, 2, ',', '.') : null;
            
            $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
            $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
            $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
            $object->correcao ? $object->correcao = number_format($object->correcao, 2, ',', '.') : null;
            $object->desconto ? $object->desconto = number_format($object->desconto, 2, ',', '.') : null;
            $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;

            $object->tarifa ? $object->tarifa = number_format($object->tarifa, 2, ',', '.') : null;
            $object->valor_creditado ? $object->valor_creditado = number_format($object->valor_creditado, 2, ',', '.') : null;
            
            $object->dt_lancamento ? $object->dt_lancamento = $string->formatDateBR($object->dt_lancamento) : null;
            $object->dt_vencimento ? $object->dt_vencimento = $string->formatDateBR($object->dt_vencimento) : null;
            $object->dt_pagamento ? $object->dt_pagamento = $string->formatDateBR($object->dt_pagamento) : null;
            $object->dt_liquidacao ? $object->dt_liquidacao = $string->formatDateBR($object->dt_liquidacao) : null;
            $object->dt_limite_desconto_boleto_cobranca ? $object->dt_limite_desconto_boleto_cobranca = $string->formatDateBR($object->dt_limite_desconto_boleto_cobranca) : null;
            
            $object->multa_boleto_cobranca ? $object->multa_boleto_cobranca = number_format($object->multa_boleto_cobranca, 2, ',', '.') : null;
            $object->juros_boleto_cobranca ? $object->juros_boleto_cobranca = number_format($object->juros_boleto_cobranca, 2, ',', '.') : null;
            $object->desconto_boleto_cobranca ? $object->desconto_boleto_cobranca = number_format($object->desconto_boleto_cobranca, 2, ',', '.') : null;
            
            $this->form->setData($object); // keep form data
            
                            
            // guarda valores de campos para o proximo novo registro
            TSession::setValue('cobranca', $object->cobranca);
            TSession::setValue('condominio_id', $object->condominio_id);
            TSession::setValue('conta_fechamento', $object->conta_fechamento_id);
            TSession::setValue('mesref', $object->mes_ref);
            TSession::setValue('vencimento', $object->dt_vencimento);
            TSession::setValue('valor', $object->valor);
            TSession::setValue('unidade', $object->unidade_id);
            TSession::setValue('classe', $object->classe_id);
            TSession::setValue('nome_responsavel', $object->nome_responsavel);
            
            TTransaction::close(); // close the transaction
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                //var_dump($param);
                $key=$param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new ContasReceber($key); // instantiates the Active Record
                
                // necessário no mysql
                $object->dt_lancamento = TDate::date2br($object->dt_lancamento); 
                $object->dt_vencimento = TDate::date2br($object->dt_vencimento);
                $object->dt_pagamento = TDate::date2br($object->dt_pagamento);
                $object->dt_liquidacao = TDate::date2br($object->dt_liquidacao);
                $object->dt_limite_desconto_boleto_cobranca = TDate::date2br($object->dt_limite_desconto_boleto_cobranca);
                
                $object->split_valor_fixo ? $object->split_valor_fixo = number_format($object->split_valor_fixo, 2, ',', '.') : null;
                
                $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                $object->correcao ? $object->correcao = number_format($object->correcao, 2, ',', '.') : null;
                $object->desconto ? $object->desconto = number_format($object->desconto, 2, ',', '.') : null;
                
                $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                $object->valor_creditado ? $object->valor_creditado = number_format($object->valor_creditado, 2, ',', '.') : null;
                
                $object->multa_boleto_cobranca ? $object->multa_boleto_cobranca = number_format($object->multa_boleto_cobranca, 2, ',', '.') : null;
                $object->juros_boleto_cobranca ? $object->juros_boleto_cobranca = number_format($object->juros_boleto_cobranca, 2, ',', '.') : null;
                $object->desconto_boleto_cobranca ? $object->desconto_boleto_cobranca = number_format($object->desconto_boleto_cobranca, 2, ',', '.') : null;
                
                $fin_remessa_itens = FinRemessaItem::where('id_contas_receber','=',$object->id)->load();
                foreach($fin_remessa_itens as $fin_remessa_item){
                    $fin_remessa = new FinRemessa($fin_remessa_item->id_fin_remessa);
                    $object->remessa = $fin_remessa->numero_remessa;
                }

                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $object = new ContasReceber();
                //$object->dt_lancamento   = date('d/m/Y');
                
                // recupera dados armazenados para o proximo registro
                $mesref = explode("/", TSession::getValue('mesref'));
                
                if ( $mesref[0] == '01' ) {
                    $mesref[0] = '02';    
                } else
                if ( $mesref[0] == '02' ) {
                    $mesref[0] = '03';    
                } else
                if ( $mesref[0] == '03' ) {
                    $mesref[0] = '04';    
                } else
                if ( $mesref[0] == '04' ) {
                    $mesref[0] = '05';    
                } else
                if ( $mesref[0] == '05' ) {
                    $mesref[0] = '06';    
                }else
                if ( $mesref[0] == '06' ) {
                    $mesref[0] = '07';    
                }else
                if ( $mesref[0] == '07' ) {
                    $mesref[0] = '08';    
                }else
                if ( $mesref[0] == '08' ) {
                    $mesref[0] = '09';    
                }else
                if ( $mesref[0] == '09' ) {
                    $mesref[0] = '10';    
                }else
                if ( $mesref[0] == '10' ) {
                    $mesref[0] = '11';    
                }else
                if ( $mesref[0] == '11' ) {
                    $mesref[0] = '12';    
                } else
                if ( $mesref[0] == '12' ) {
                    $mesref[0] = '01';
                    $mesref[1] =  $mesref[1]+1;  // incremente o ano    
                } 
                
                $object->mes_ref = $mesref[0] . '/' . $mesref[1];
                
                //TSession::setValue('mesref', $object->mes_ref);
                //TSession::setValue('vencimento', $object->dt_vencimento);
                 
                // recupera dados armazenados para o proximo registro
                $dt_venc = explode("/", TSession::getValue('vencimento'));
                
                //var_dump($dt_venc);
                
                if ( $dt_venc[1] == '01' ) {
                    $dt_venc[1] = '02';    
                } else
                if ( $dt_venc[1] == '02' ) {
                    $dt_venc[1] = '03';    
                } else
                if ( $dt_venc[1] == '03' ) {
                    $dt_venc[1] = '04';    
                } else
                if ( $dt_venc[1] == '04' ) {
                    $dt_venc[1] = '05';    
                } else
                if ( $dt_venc[1] == '05' ) {
                    $dt_venc[1] = '06';    
                } else
                if ( $dt_venc[1] == '06' ) {
                    $dt_venc[1] = '07';    
                } else
                if ( $dt_venc[1] == '07' ) {
                    $dt_venc[1] = '08';    
                } else
                if ( $dt_venc[1] == '08' ) {
                    $dt_venc[1] = '09';    
                }else
                if ( $dt_venc[1] == '09' ) {
                    $dt_venc[1] = '10';    
                } else
                if ( $dt_venc[1] == '10' ) {
                    $dt_venc[1] = '11';    
                } else
                if ( $dt_venc[1] == '11' ) {
                    $dt_venc[1] = '12';    
                } else
                if ( $dt_venc[1] == '12' ) {
                    $dt_venc[1] = '01';
                    $dt_venc[2] = $dt_venc[2]+1; // incremente o ano    
                } 
                
                $object->dt_vencimento = $dt_venc[0] . '/' . $dt_venc[1] . '/' . $dt_venc[2];
                
                $object->valor = TSession::getValue('valor');
                $object->unidade_id = TSession::getValue('unidade');
                $object->classe_id = TSession::getValue('classe');
                
                $this->form->setData($object);
                
                //$this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
   function onGerarParcelas2($param) {
        try {
            //LIMPA A GRID
            $this->datagrid->clear();
            //VERIFICA OS CAMPOS OBRIGATORIOS E FAZ A VALIDAÇÃO
            //$this->form->validate();
            //CONVERTE O VALOR PARA O FORMATO DE DECIMAIS USA PARA UMA DIVISAO CORRETA
            //$total = FormataMascaras::retornaNumerosDecimaisUSA($param['valor_total']);
            //RECEBE O PRAZO INFORMADO PELO USER
            //$prazo = $param['prazo_nome'];
            //CHAMA A FUNCAO QUE REALIZA O CALCULO DA DIVISAO E TBM REALIZA O CALCULO DOS VENCIMENTOS - RETORNA UM ARRAY
            //$parcelas = GerarParcelas::calcularParcelas($total, $prazo, null);
            $parcelas = GerarParcelas::calcularParcelas(2000.00, '30/60/90', null);
            //ARMAZENA AS PARCELAS SEM UMA VARIAVEL DE SESSAO
            TSession::setValue(__CLASS__ . '_parcelas', $parcelas);
            //LAÇO ONDE ADD NA GRID A DIVISAO - OS CENTAVOS SAO ADICIONADOS NA ULTIMA PARCELA
            foreach ($parcelas as $parcela) {
                //ADICIONA AS PARCELAS NA GRID
                $item = new StdClass;
                $item->detail_id = empty($parcela->detail_id) ? 'X' . mt_rand(1000000000, 1999999999) : $parcela->detail_id;
                $item->id = $parcela->id;
                $item->numero_parcela = $parcela->numero_parcela;
                //$item->valor_parcela = FormataMascaras::retornaNumerosDecimaisBR($parcela->valor_parcela, 2);
                $item->valor_parcela = $parcela->valor_parcela;
                $item->vencimento_parcela = $parcela->vencimento_parcela;
                $this->datagrid->addItem($item);
            }
            //MANTEM OS DADOS NO FORM
            $this->form->setData($this->form->getData());
        } catch (Exception $ex) {
            new TMessage('error', 'ma oee ' . $ex->getMessage());
        }
    }   
    
    public static function onUpdateTotal($param)
        {
            $multa  = (double) str_replace(',', '.', $param['multa']);
            $juros   = (double) str_replace(',', '.', $param['juros']);
            $correcao = (double) str_replace(',', '.', $param['correcao']);
            $desconto = (double) str_replace(',', '.', $param['desconto']);
            $valor = (double) str_replace(',', '.', $param['valor']);
            
            $soma = ($valor+$multa+$juros+$correcao)-$desconto;
              
            //var_dump($soma);
                    
            $obj = new StdClass;
           
            $obj->valor_pago = number_format($soma, 2, ',', '.');
            $obj->valor_creditado = number_format($soma, 2, ',', '.');
                                      
            TForm::sendData('form_contas_receber', $obj);
    }  
    
   
}
