<?php
/**
 * FechamentoForm Form
 * @author  <your name here>
 */
class FechamentoForm extends TPage
{
    //private $notebook;
    private $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('form_Fechamento');
        $this->form->setFormTitle('Fechamentos');
        
        // create the form fields
        $id = new TEntry('id');
        
        //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
        //$mes_ref = new TEntry('mes_ref');
        $criteria = new TCriteria;
        //$criteria->add(new TFilter('id', '=', $user->condominio_id));
        //$criteria->add(new TFilter('id', 'IN', TSession::getValue('userunitids')));
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio')));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
        
        $mes_ref                        = new TEntry('mes_ref');
        
        $previsao_arrecadacao = new TEntry('previsao_arrecadacao');
        $taxa_inadimplencia = new TEntry('taxa_inadimplencia');
        
        $saldo_inicial = new TEntry('saldo_inicial');
        $receita = new TEntry('receita');
        $despesa = new TEntry('despesa');
        $saldo_final = new TEntry('saldo_final');
        
        $dt_fechamento = new TDate('dt_fechamento');
        $dt_inicial = new TDate('dt_inicial');
        $dt_final = new TDate('dt_final');
        
        $nota_explicativa = new TText('nota_explicativa');
        $status = new TEntry('status');
        $atualizacao = new TEntry('atualizacao');
        
        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);
        
        // definição de formato numerico
        $previsao_arrecadacao->setNumericMask(2,',', '.', true);
        $taxa_inadimplencia->setNumericMask(2,',', '.', true);
        
        $saldo_inicial->setNumericMask(2,',', '.', true);
        $receita->setNumericMask(2,',', '.', true);
        $despesa->setNumericMask(2,',', '.', true);
        $saldo_final->setNumericMask(2,',', '.', true);
        
        $dt_fechamento->setMask('dd/mm/yyyy');
        $dt_fechamento->setValue(date('d/m/Y'));
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setValue(date('d/m/Y'));
        $dt_final->setMask('dd/mm/yyyy');
        $dt_final->setValue(date('d/m/Y'));
        
        // define the sizes
        $saldo_final->setSize('50%', 50);
        
        $previsao_arrecadacao->setValue(0);
        $taxa_inadimplencia->setValue(0);
        $saldo_inicial->setValue(0);
        $saldo_final->setValue(0);
        $receita->setValue(0);
        $despesa->setValue(0);
        $nota_explicativa->setValue('.');
        $mes_ref->setValue( TSession::getValue('mesref') );
        $condominio_id->setValue( TSession::getValue('condominio_id') );
        $conta_fechamento_id->setValue( TSession::getValue('conta_fechamento') );
        
        // add the fields
        //$this->form->addQuickField('Id', $id,  100 );
        //$this->form->addQuickField('Imovel Id', $condominio_id,  100 );
        //$this->form->addQuickField('Mes Ref', $mes_ref,  200 );
        
        $this->form->addFields( [new TLabel('Id')], [$id]);
        
        $this->form->addFields( [new TLabel('Mês Referência')], [$mes_ref], [new TLabel('Condomínio')], [$condominio_id] );
        //$this->form->addFields( [new TLabel('Mês Referência')], [$mes_ref]);
        
        $this->form->addFields( [new TLabel('Previsao Arrecadacao')], [$previsao_arrecadacao], 
                                [new TLabel('Taxa Inadimplência')], [$taxa_inadimplencia],
                                [new TLabel('Conta Fechamento')], [$conta_fechamento_id] ); 
        
        $this->form->addFields( [new TLabel('Data Fechamento')], [$dt_fechamento], 
                                [new TLabel('Data Inicial')], [$dt_inicial],
                                [new TLabel('Data Final')], [$dt_final] );
                                  
               
        $this->form->addFields(   [new TLabel('Saldo Inicial')], [$saldo_inicial], 
                                  [new TLabel('Receita')]      , [$receita], 
                                  [new TLabel('Despesa')]      , [$despesa] );
                                  
        $this->form->addFields( [new TLabel('Saldo Final')]  , [$saldo_final] );
                
        //$this->form->addQuickField('Nota Explicativa', $nota_explicativa,  200 );
        $this->form->addFields( [new TLabel('Nota Explicativa')], [$nota_explicativa]);
        //$this->form->addQuickField('Status', $status,  200 );
        //$this->form->addQuickField('Atualizacao', $atualizacao,  200 );

               
        if (empty($id))
        {
            //TTransaction::open('facilitasmart');
            //$condominio = new Condominio(TSession::getValue('condominio_id'));
            //$multa_boleto_cobranca->setValue(number_format($condominio->multa, 2, ',', '.'));
            //$juros_boleto_cobranca->setValue(number_format($condominio->juros), 2, ',', '.');
            //$desconto_boleto_cobranca->setValue(number_format($condominio->desconto, 2, ',', '.'));
            //TTransaction::close();
        }
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        //Pablo Dall'Oglio O addquickfield bota um tamanho default. Chama o setsize depois
        $nota_explicativa->setSize(800, 120);
        
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction( _t('New'), new TAction(array($this, 'onClear')), 'fa:plus-square green' );
        $this->form->addAction( _t('List'), new TAction(array('FechamentoList','onReload')), 'fa:table blue');
        $this->form->addAction( 'Calcular Fechamento', new TAction(array($this, 'onCalcular')), 'fa:usd  green');
        $this->form->addAction( 'Parcial', new TAction(array('DemonstrativoFinanceiroReport1', 'onGenerator')), 'fa:usd  green');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        // add the vbox inside the page
        
                // mostrar o mes ref e condominio selecionado
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
            
            parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add($container);
       
    }
    
    public function onParcial($param)
    {
        $this->form->setData($param); // fill form data 
    }
    
    public function onCalcular($param)
    {
         
        $string = new StringsUtil;
        
        $data_inicial = TDate::date2us($param['dt_inicial']);
        $data_final  = TDate::date2us($param['dt_final']);
        
        $datahoje = date($param['dt_inicial']);
        $partes = explode("/", $datahoje);
        
        $ano_hoje = $partes[2];
        $mes_hoje = $partes[1];
        $mes_ant  = ((int) $mes_hoje ) -1;
        
        if ( $mes_ant == 0 ) {
          $mes_ant = '12';
          $ano_hoje = $ano_hoje - 1;
        }

        $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
        $dia_hoje = $partes[0];
                
        $mesref = $mes_ant . '/' . $ano_hoje; 
        
        TTransaction::open('facilitasmart');
        
        $conn = TTransaction::get();                      
        $sqlFecha = "SELECT * FROM fechamento where  
                        condominio_id = " . $param['condominio_id'] . " and " .
                        "conta_fechamento_id = " . $param['conta_fechamento_id'] . " and " .
                        "mes_ref = '" . $mesref . " '";
        $fechamentos = $conn->query($sqlFecha);

        $obj = new StdClass;
        
        $obj->saldo_inicial = 0;
        
        foreach ($fechamentos as $fechamento) // feito pelo select
        {
          $obj->saldo_inicial = $fechamento['saldo_final'];
          
        }
        
        // receita
        $connreceber = TTransaction::get();
        $sqlreceber = "SELECT sum(contas_receber.valor_creditado) as recebimentos
                       FROM contas_receber
                        where  
                        contas_receber.condominio_id = " . $param['condominio_id']  . " and " .
                        "contas_receber.conta_fechamento_id = " . $param['conta_fechamento_id'] . " and " .
                        "contas_receber.situacao = '1' and 
                        (contas_receber.dt_liquidacao >= '{$data_inicial}' and 
                        contas_receber.dt_liquidacao <= '{$data_final}')"
                        ;
                        
        //var_dump($sqlreceber);
                        
        $colunasrecebers = $connreceber->query($sqlreceber);
        
        $obj->receita = 0;
        
        foreach ($colunasrecebers as $colunareceber)
            {
                $obj->receita = $colunareceber['recebimentos'];
            }
            
        //var_dump( $obj->receita );
        
        // despesa
        $conn0 = TTransaction::get();
        $sql0 = "SELECT sum(contas_pagar.valor_pago) as pagamentos
                       FROM contas_pagar 
                        where  
                        contas_pagar.condominio_id = " . $param['condominio_id'] . " and " .
                        "contas_pagar.conta_fechamento_id = " . $param['conta_fechamento_id'] . " and " .
                        "contas_pagar.situacao = '1' and 
                        (contas_pagar.dt_liquidacao >= '{$data_inicial}' and 
                        contas_pagar.dt_liquidacao <= '{$data_final}')"
                        ;
        $colunas0 = $conn0->query($sql0);
        
        $obj->despesa = 0;
        
        //var_dump($colunas0);
        
        foreach ($colunas0 as $coluna0)
            {
                $obj->despesa = $coluna0['pagamentos'];
            }
            
        $obj->saldo_final = $obj->saldo_inicial + ($obj->receita-$obj->despesa);
        
        $obj->saldo_inicial ? $obj->saldo_inicial = number_format($obj->saldo_inicial, 2, ',', '.') : null;
        $obj->receita ? $obj->receita = number_format($obj->receita, 2, ',', '.') : null;
        $obj->despesa ? $obj->despesa = number_format($obj->despesa, 2, ',', '.') : null;
        $obj->saldo_final ? $obj->saldo_final = number_format($obj->saldo_final, 2, ',', '.') : null;
        
        $obj->id = $param['id'];
        $obj->condominio_id = $param['condominio_id'];
        $obj->mes_ref = $param['mes_ref'];
        $obj->previsao_arrecadacao = $param['previsao_arrecadacao'];
        $obj->taxa_inadimplencia = $param['taxa_inadimplencia'];
        
        $obj->dt_fechamento = $param['dt_fechamento'];
        $obj->dt_inicial = $param['dt_inicial'];
        $obj->dt_final = $param['dt_final'];
        
        $obj->nota_explicativa = $param['nota_explicativa'];
        $obj->conta_fechamento_id = $param['conta_fechamento_id'];
        
        TTransaction::close();
        
        //$this->form->setData($data); // fill form data
        
        TForm::sendData('form_Fechamento', $obj, FALSE, FALSE);
       
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            $string = new StringsUtil;
             
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new Fechamento;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // conversoes
            $object->dt_fechamento = TDate::date2us($object->dt_fechamento);
            $object->dt_inicial = TDate::date2us($object->dt_inicial);
            $object->dt_final = TDate::date2us($object->dt_final);
            //$object->saldo_inicial ? $object->saldo_inicial = $string->desconverteReais($object->saldo_inicial) : null;
            //$object->saldo_final ? $object->saldo_final = $string->desconverteReais($object->saldo_final) : null;
            //$object->receita ? $object->receita = $string->desconverteReais($object->receita) : null;
            //$object->despesa ? $object->despesa = $string->desconverteReais($object->despesa) : null;
            
            $object->store(); // save the object
            
            /*
            $object->saldo_inicial ? $object->saldo_inicial = number_format($object->saldo_inicial, 2, ',', '.') : null;
            $object->receita ? $object->receita = number_format($object->receita, 2, ',', '.') : null;
            $object->despesa ? $object->despesa = number_format($object->despesa, 2, ',', '.') : null;
            $object->saldo_final ? $object->saldo_final = number_format($object->saldo_final, 2, ',', '.') : null;
            
            $object->dt_fechamento ? $object->dt_fechamento = $string->formatDateBR($object->dt_fechamento) : null;
            $object->dt_inicial ? $object->dt_inicial = $string->formatDateBR($object->dt_inicial) : null;
            $object->dt_final ? $object->dt_final = $string->formatDateBR($object->dt_final) : null;
            */
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            
            // guarda valores de campos para o proximo novo registro
            //TSession::setValue('cobranca', $object->cobranca);
            TSession::setValue('condominio_id', $object->condominio_id);
            TSession::setValue('conta_fechamento', $object->conta_fechamento_id);
            TSession::setValue('mesref', $object->mes_ref);
            //TSession::setValue('vencimento', $object->dt_vencimento);
            //TSession::setValue('valor', $object->valor);
            //TSession::setValue('unidade', $object->unidade_id);
            //TSession::setValue('classe', $object->classe_id);
            //TSession::setValue('nome_responsavel', $object->nome_responsavel);
            
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
        $this->form->clear();
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
                $object = new Fechamento($key); // instantiates the Active Record
                
                // conversoes
                $object->dt_fechamento = TDate::date2br($object->dt_fechamento);
                $object->dt_inicial = TDate::date2br($object->dt_inicial);
                $object->dt_final = TDate::date2br($object->dt_final);                
                
                //$object->saldo_inicial ? $object->saldo_inicial = number_format($object->saldo_inicial, 2, ',', '.') : null;
                //$object->receita ? $object->receita = number_format($object->receita, 2, ',', '.') : null;
                //$object->despesa ? $object->despesa = number_format($object->despesa, 2, ',', '.') : null;
                //$object->saldo_final ? $object->saldo_final = number_format($object->saldo_final, 2, ',', '.') : null;
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
