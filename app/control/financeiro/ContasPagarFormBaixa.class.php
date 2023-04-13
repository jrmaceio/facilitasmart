<?php
/**
 * ContasPagarForm2 Form
 * @author  <your name here>
 */
class ContasPagarFormBaixa extends TPage
{
    protected $form; // form
    private $attach_area;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
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
        //$this->form = new TQuickForm('form_ContasPagarBaixar');
        //$this->form->class = 'tform'; // change CSS class
        //$this->form = new BootstrapFormWrapper($this->form);
        //$this->form->style = 'display: table;width:100%'; // change style
        
        $this->form = new BootstrapFormBuilder('form_ContasPagarBaixar');
        $this->form->setFormTitle('Liquidação de Despesas');
        
        // define the form title
        $this->form->setFormTitle('Liquidação Despesas');
        
        // create the form fields
        $id = new TEntry('id');
        
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        $mes_ref = new TEntry('mes_ref');
        
        // não pegava a situacao porque não estava definido aqui
        $situacao = new THidden('situacao');
        
        //$planocontas_id = new TEntry('planocontas_id');
        //$planocontas_id = new TDBMultiSearch('planocontas_id','facilita','PlanoContas','id','descricao');
        //$criteria = new TCriteria;
        //$criteria->add(new TFilter("tipo", "=", 'D'));
        $planocontas_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 
        '{codigo} - {descricao}','descricao');  
        
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_liquidacao = new TDate('dt_liquidacao');
        $dt_pagamento = new TDate('dt_pagamento');
        $valor = new TEntry('valor');

        $parcela = new TEntry('parcela');
        $descricao = new TEntry('descricao');
        
        $tipo_pagamento_id = new TDBCombo('tipo_pagamento_id', 'facilitasmart', 'TipoPagamento', 'id', '{id}-{descricao}','descricao');
        $numero_doc_pagamento = new TEntry('numero_doc_pagamento');
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao');
        
        //liquidacao
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
        
        
        $valor->setNumericMask(2, ',', '.');
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_liquidacao->setMask('dd/mm/yyyy');
        $dt_pagamento->setMask('dd/mm/yyyy');
        
        // validations
        $dt_vencimento->addValidation('dt_vencimento', new TRequiredValidator);
        $dt_liquidacao->addValidation('dt_liquidacao', new TRequiredValidator);
        $dt_pagamento->addValidation('dt_pagamento', new TRequiredValidator);
        
        $descricao->setSize('100%');
        
        //$planocontas_id->setSize(240,28);
        //$planocontas_id->setMinLength(1);
        //$planocontas_id->setMaxSize(1);

        $parcela->setSize('20%');
        
        //atribue o imovel selecionado
//        $imovel_id->setValue( TSession::getValue('id_imovel') );
//        $imovel_id->setEditable(FALSE); 
//
//        $mes_ref->setValue( TSession::getValue('mesref') );
        
        $dt_vencimento->setEditable(FALSE); 
        $mes_ref->setEditable(FALSE); 
        
        $planocontas_id->setEditable(FALSE);
        $condominio_id->setEditable(FALSE);
        
        $valor->setEditable(FALSE); 
        //$descricao->setEditable(FALSE); 
        
        
        // define how many columns
        //$this->form->setFieldsByRow(2);

        $lbl_parcela = new TLabel('Parcela');
        $lbl_parcela->setFontStyle('b');
        $lbl_valor = new TLabel('Valor');
        $lbl_valor->setFontStyle('b');
        
        // define the sizes
        $id->setSize('100%');
        $mes_ref->setSize('100%');
        $condominio_id->setSize('100%');
        $planocontas_id->setSize('100%');
        
        $multa->setSize('50%');
        $juros->setSize('50%');
        $correcao->setSize('50%');
        $desconto->setSize('50%');
        
        $valor_pago->setSize('50%');
          
        // add the fields
        $this->form->addFields( [new TLabel('Id')], [$id],
                                [new TLabel('Mes Ref')], [$mes_ref]);

        $this->form->addFields( [new TLabel('Condominio')], [$condominio_id], 
                                [new TLabel('Classe')], [$planocontas_id]);

        
        $this->form->addFields( [new TLabel('Vencimento')], [$dt_vencimento], 
                                [new TLabel('Valor')], [$valor] );
       
        $this->form->addFields( [new TLabel('Descrição')], [$descricao]);
        
        $this->form->addFields( [new TLabel('Tipo Pagamento')], [$tipo_pagamento_id],
                                [new TLabel('Número Doc. Pagamento')], [$numero_doc_pagamento]);
        
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]);
        $this->form->addFields( [new TLabel('Data Liquidação')], [$dt_liquidacao],
                                [new TLabel('Data Pagamento')], [$dt_pagamento]);

        $this->form->addFields( [new TLabel('Multa')], [$multa],
                                [new TLabel('Juros')], [$juros]);
        $this->form->addFields( [new TLabel('Correção')], [$correcao],
                                [new TLabel('Desconto')], [$desconto]);
 
        $this->form->addFields( [new TLabel('Valor Pago')], [$valor_pago]);
        //$this->form->addQuickField('', $situacao );  
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
        
        $change_mes_ref = new TAction(array($this, 'onTestaMesRef'));
        $mes_ref->setExitAction($change_mes_ref);
        
        //liquidacao
        $multa->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $juros->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $correcao->setExitAction(new TAction(array($this, 'onUpdateTotal')));
        $desconto->setExitAction(new TAction(array($this, 'onUpdateTotal')));
         
        // create the form actions
        //$this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        //$this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        
        $btn1 = $this->form->addAction(_t('List'), new TAction(array('ContasPagarList','onReload')), 'fa:table blue');
        $btn1->class = 'btn btn-sm btn-primary';
        
        //$this->alertBox = new TElement('div');
        
        $this->attach_area = new TVBox;
        $this->form->addContent( [$this->attach_area] );
        
        // creates the page structure using a vbox
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        // add the vbox inside the page
        parent::add($container);
        
    }
    
    public static function onUpdateTotal($param)
        {
            $multa  = (double) str_replace(',', '.', $param['multa']);
            $juros   = (double) str_replace(',', '.', $param['juros']);
            $correcao = (double) str_replace(',', '.', $param['correcao']);
            $desconto = (double) str_replace(',', '.', $param['desconto']);
            $valor = (double) str_replace(',', '.', $param['valor']);
            
            $soma = ($valor+$multa+$juros+$correcao)-$desconto;
              
            //var_dump($param);
                    
            $obj = new StdClass;
           
            $obj->valor_pago = number_format($soma, 2, ',', '.');
                                      
            TForm::sendData('form_ContasPagarBaixar', $obj);
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
	          $y = isset($mesreferencia[1]) ? $mesrefhttps://www.google.com.br/maps/place/R.+Manoel+do+Nascimento+Abreu+-+S%C3%A3o+Luiz,+Arapiraca+-+AL/@-9.7706145,-36.6481706,17z/data=!4m5!3m4!1s0x705d50234433921:0x6dd31e23a02692df!8m2!3d-9.7716374!4d-36.6463521erencia[1] : 0;
	        
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
                          new TMessage('info', 'Mês Referência Inválido');
	                        $obj->mes_ref='';
                        }
	          } else {
	            //echo "data inválida!";
	             new TMessage('info', 'Mês Referência Inválido');
	             $obj->mes_ref='';
	       
	          }    https://www.google.com.br/maps/place/R.+Manoel+do+Nascimento+Abreu+-+S%C3%A3o+Luiz,+Arapiraca+-+AL/@-9.7706145,-36.6481706,17z/data=!4m5!3m4!1s0x705d50234433921:0x6dd31e23a02692df!8m2!3d-9.7716374!4d-36.6463521
            
            TForm::sendData('form_ContasPagar', $obj, FALSE, FALSE);
        }
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        $string = new StringsUtil;
        
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
        
            $object = new ContasPagar;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
                       
            //// verifica fechamento
            $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                       where('mes_ref', '=', $object->mes_ref)->
                                       where('conta_fechamento_id', '=', $object->conta_fechamento_id)->load();
            
                     
            //default = 1 fechado, não permite nada
            $status = 1;
        
            foreach ($fechamentos as $fechamento)
            {
                $status = $fechamento->status;
            }
                        
                        
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref da data baixa !');
                TTransaction::close(); // close the transaction
                return;
            }
            ////////////////////////////////////          
                           
            if ( $object->situacao == '1') {
                new TMessage('info', 'Título já baixado, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            //var_dump($object->dt_vencimento);
            // valida a data de vencimento e mes_ref
            $dt_venc = explode("/", $object->dt_vencimento);
            $mes_referencia = explode("/", $object->mes_ref);
            
            //var_dump($dt_venc[1]);
            //var_dump($mes_referencia[0]);
            if ( $dt_venc[1] != $mes_referencia[0]) {
                new TMessage('info', 'Divergência entre o mês da data de vencimento e o Mês Ref !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            } 
            
            if ( $dt_venc[2] != $mes_referencia[1]) {
                new TMessage('info', 'Divergência entre o ano da data de vencimento e o Mês Ref !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            // verifica se existe fechamento aberto possivel de edicao
            $fechamentos = Fechamento::where('condominio_id', '=', $object->condominio_id)->
                                      where('mes_ref', '=', $object->mes_ref)->load();
            
            $status = '';
            foreach ($fechamentos as $fechamento) {
                $status = $fechamento->status;
            }
            
            if ($status == 1 and $object->valor_pago == 0){
                //var_dump($status);
                new TMessage('info', 'Não existe um Fechamento aberto para a data de vencimento !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return;     
            }
            
            $object->usuario = TSession::getValue('login');
            
            //formato necessário no mysql
            $object->dt_vencimento = TDate::date2us($object->dt_vencimento );
            $object->dt_liquidacao = TDate::date2us($object->dt_liquidacao );
            $object->dt_pagamento = TDate::date2us($object->dt_pagamento );
            
            $object->valor ? $object->valor = $string->desconverteReais($object->valor) : null;
            $object->valor_pago ? $object->valor_pago = $string->desconverteReais($object->valor_pago) : null;
            $object->multa ? $object->multa = $string->desconverteReais($object->multa) : null;
            $object->juros ? $object->juros = $string->desconverteReais($object->juros) : null;
            $object->desconto ? $object->desconto = $string->desconverteReais($object->desconto) : null;
            $object->correcao ? $object->correcao = $string->desconverteReais($object->correcao) : null;

            //$object->dt_lancamento = date("Y-m-d");
            
            // pega a chave do TDBMultiSearch 
            //troquei opor combo ===== >$object->planocontas_id = key($data->planocontas_id);
            ///var_dump($object->planocontas_id);
            
            $object->situacao = '1';
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new ContasPagar($key); // instantiates the Active Record
                
                // necessário no mysql
                $object->dt_lancamento = TDate::date2br($object->dt_lancamento); 
                $object->dt_vencimento = TDate::date2br($object->dt_vencimento);
                $object->dt_pagamento = TDate::date2br($object->dt_pagamento);
                $object->dt_liquidacao = TDate::date2br($object->dt_liquidacao);
                
                $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                $object->multa ? $object->multa = number_format($object->multa, 2, ',', '.') : null;
                $object->juros ? $object->juros = number_format($object->juros, 2, ',', '.') : null;
                $object->correcao ? $object->correcao = number_format($object->correcao, 2, ',', '.') : null;
                $object->desconto ? $object->desconto = number_format($object->desconto, 2, ',', '.') : null;
                $object->valor_pago ? $object->valor_pago = number_format($object->valor_pago, 2, ',', '.') : null;
                
                
                if (file_exists($object->file))
                {
                    //var_dump("files/ctspagar/{$key}/$object->file");
                    $this->attach_area->add( TElement::tag('h4', 'Anexo(s)') );
                    //$this->attach_area->add( new THyperLink($object->file, "download.php?file=$object->file"));
                    $this->attach_area->add( new THyperLink($object->file, "download.php?file=files/ctspagar/{$key}/$object->file"));
                }
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
