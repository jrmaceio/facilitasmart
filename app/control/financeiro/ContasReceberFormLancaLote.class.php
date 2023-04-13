<?php

    //// habilitar erros da pagina
//    ini_set('display_errors', 1);
  //  ini_set('display_startup_erros', 1);
   // error_reporting(E_ALL);


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
class ContasReceberFormLancaLote extends TPage
{
    private $form; // form
    private $datagrid; // listing
    
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
        $this->form->setFormTitle('Lançamento em lote de contas a receber');
  
        // create the form fields
        $id                             = new TEntry('id');
        
               
        $mes_ref                        = new TEntry('mes_ref');

        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', TSession::getValue('id_condominio')));
        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);

        $cobranca                  = new TCombo('Cobrança');
        $combo_cob = array();
        $combo_cob[1] = '1';
        $combo_cob[2] = '2';
        $combo_cob[3] = '3';
        $combo_cob[4] = '4';
        $combo_cob[5] = '5';
        $combo_cob[6] = '6';
        $cobranca->addItems($combo_cob);
        $cobranca->setDefaultOption(FALSE);
        
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $tipo_lancamento->setValue('A'); // M - Manual A - Automatico I - Interno 
        $tipo_lancamento->setEditable(FALSE); 
        
        $criteriaGrupo = new TCriteria;
        $criteriaGrupo->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $grupo_id =  new TEntry('grupo_id');//new TDBCombo('grupo_id', 'facilitasmart', 'GrupoContasReceber', 'id', 'descricao','descricao',$criteriaGrupo);
        
        /////$classe_chave = new TEntry('classe_chave');
        //$/classe_chave->setEditable(FALSE); 
              
        //$classe_id                      = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);
        
        /////////////$unidade_id                     = new TEntry('unidade_id');

        ////////////$unidade_nome_prop = new TEntry('unidade_nome_prop');
        /////////////$unidade_nome_prop->setEditable(FALSE);
        
        // set exit action for input_exit
        ///////////////$exit_id_unidade = new TAction(array($this, 'onExitIdUnidade'));
        ////////////$unidade_id->setExitAction($exit_id_unidade);
        
        ////////////$unidade_descricao = new TEntry('unidade_descricao');
        //////////////////$unidade_descricao->setEditable(FALSE);
        
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

        $criteria2 = new TCriteria;
        $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        $conta_fechamento_id = new TDBCombo('conta_fechamento_id', 'facilitasmart', 'ContaFechamento', 'id', '{id}-{descricao}','descricao', $criteria2);

        // define the sizes
        $id->setSize(100);
        //$condominio_id->setSize(100);
        $mes_ref->setSize(100);
        $grupo_id->setSize(50);
        $cobranca->setSize(50);
        $tipo_lancamento->setSize(50);
        $classe_id->setSize(400);
        /////////$unidade_id->setSize(60);
        $dt_lancamento->setSize(100);
        $dt_vencimento->setSize(100);
        $valor->setSize(100);
        $descricao->setSize(400);
        ///////$unidade_descricao->setSize(200);
        ///////$unidade_nome_prop->setSize(400);


        // validations
        $classe_id->addValidation('classe_id', new TRequiredValidator);
        ///////////$unidade_id->addValidation('unidade_id', new TRequiredValidator);
        $dt_lancamento->addValidation('dt_lancamento', new TRequiredValidator);
        $dt_vencimento->addValidation('dt_vencimento', new TRequiredValidator);
        $valor->addValidation('valor', new TRequiredValidator);


        // add one row for each form field
        $this->form->addFields( [new TLabel('Id:')], [$id] );
        $this->form->addFields( [new TLabel('Condomínio:')], [$condominio_id] );
        $this->form->addFields( [new TLabel('Mês Ref.:')], [$mes_ref] );
        $this->form->addFields( [new TLabel('Cobrança:')], [$cobranca] );
        $this->form->addFields( [new TLabel('Tipo Lançamento:')], [$tipo_lancamento] );
        $this->form->addFields( [new TLabel('Grupo:')], [$grupo_id] );
        $this->form->addFields( [new TLabel('Classe:')], [$classe_id] );
         $this->form->addFields( [new TLabel('Lançamento:')], [$dt_lancamento] );
        $this->form->addFields( [new TLabel('Vencimento:')], [$dt_vencimento] );
        $this->form->addFields( [new TLabel('Valor:')], [$valor] );
        $this->form->addFields( [new TLabel('Descrição:')], [$descricao] );

        $multa_boleto_cobranca = new TEntry('multa_boleto_cobranca');
        $juros_boleto_cobranca = new TEntry('juros_boleto_cobranca');
        $desconto_boleto_cobranca = new TEntry('desconto_boleto_cobranca');
        $dt_limite_desconto_boleto_cobranca = new TDate('dt_limite_desconto_boleto_cobranca');
        
        $dt_limite_desconto_boleto_cobranca->setMask('dd/mm/yyyy');
        
        $multa_boleto_cobranca->setSize('50%');
        $juros_boleto_cobranca->setSize('50%');
        $desconto_boleto_cobranca->setSize('50%');
        $dt_limite_desconto_boleto_cobranca->setSize('50%');
        
        // falta converter na hora de gravar
        $multa_boleto_cobranca->setNumericMask(2, ',', '.');
        $juros_boleto_cobranca->setNumericMask(2, ',', '.');  
        $desconto_boleto_cobranca->setNumericMask(2, ',', '.');  
        
        $this->form->addFields( [new TLabel('Multa:')], [$multa_boleto_cobranca] );
        $this->form->addFields( [new TLabel('Juros ao mês:')], [$juros_boleto_cobranca] );
        $this->form->addFields( [new TLabel('Desconto:')], [$desconto_boleto_cobranca] );  
        $this->form->addFields( [new TLabel('Dt Limite Desconto:')], [$dt_limite_desconto_boleto_cobranca] );
        
        $this->form->addFields( [new TLabel('Conta Fechamento')], [$conta_fechamento_id]);
        
       
        $mes_ref->setValue( TSession::getValue('mesref') );
        $mes_ref->setEditable(FALSE);

        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'), new TAction(array($this, 'onEdit')), 'bs:plus-sign green');
        
        //parent::add($this->form);
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    public static function onExitIdUnidade($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            $unidade_desc = Unidade::RetornaDescricaoUnidade($param['unidade_id']);
            $unidade_prop_nome = Unidade::RetornaProprietarioUnidade($param['unidade_id']);
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        $obj = new StdClass;
        $obj->unidade_descricao = $unidade_desc;
        $obj->unidade_nome_prop = $unidade_prop_nome;
        TForm::sendData('form_contas_receber', $obj);
        //new TMessage('info', 'Message on field exit. <br>You have typed: ' . $param['input_exit']);
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
            //TTransaction::setLogger(new TLoggerTXT('/var/www/html/facilita/log.txt')); 
            //TTransaction::log("** inserting contas receber"); 
            
            // get the form data into an active record contas_receber
            $object = $this->form->getData('ContasReceber');
            
            //formato necessário no mysql
            $object->dt_lancamento = TDate::date2us($object->dt_lancamento); 
            $object->dt_vencimento = TDate::date2us($object->dt_vencimento);
            $object->dt_limite_desconto_boleto_cobranca = TDate::date2us($object->dt_limite_desconto_boleto_cobranca);
            
            // atribui valores
            $object->dt_ultima_alteracao = date('Y/m/d');
            $object->usuario_id = TSession::getValue('login');  
                
            $object->valor ? $object->valor = $string->desconverteReais($object->valor) : null;
                        
            $this->form->validate(); // form validation
            
            /////////////// Gravar um lancamento para cada unidade /////////////////
            
            ////////// Verificar se é possivel lancar, fechamento em aberto
            $data_titulo = explode("-", $object->dt_vencimento);
            $status = ContasReceber::retornaStatusFechamento(TSession::getValue('id_condominio'), 
            $data_titulo[1] .'/'. 
            $data_titulo[0], $object->conta_fechamento_id);
            
            //verifica e nao deixa lancar se não existir fechamento aberto na data
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Ref do lançamento. Cancelado. ' . 
                $data_titulo[1] .'/'. $data_titulo[0] . ' !');
                TTransaction::close(); // close the transaction
                return;
            }
            
            
            try
            {
                $object->multa_boleto_cobranca ? $object->multa_boleto_cobranca = $string->desconverteReais($object->multa_boleto_cobranca) : null;
                $object->juros_boleto_cobranca ? $object->juros_boleto_cobranca = $string->desconverteReais($object->juros_boleto_cobranca) : null;
                $object->desconto_boleto_cobranca ? $object->desconto_boleto_cobranca = $string->desconverteReais($object->desconto_boleto_cobranca) : null;
            
            
                $unidades_lanc = Unidade::RetornaUnidadesCondominio($object->condominio_id);
                
                foreach ($unidades_lanc as $row)
                {
                    //$conn = TTransaction::get();
        
                    //$result = $conn->query("select *
                    //                        from contas_receber where 
                    //                           unidade_id = '{$object->unidade_id}' and 
                    //                           classe_id = '{$object->classe_id}' and
                    //                           mes_ref = '{$object->mes_ref}' and
                    //                           cobranca = '{$object->cobranca}'
                    //                       ");
                    //$resultado = ''; // evita erro de variavel inexistente em caso que o sql é vazio
            
                    //foreach ($result as $row1)
                    //{
                    //   $resultado = $row1['id']; // TESTA SE JA NAO EXISTE UM LANCAMENTO
                    //}
                    
                    if ($row['gera_titulo'] == 'Y' and $row['grupo_id'] == $object->grupo_id) {
                        $detail = new ContasReceber;
                    
                        $detail->condominio_id = $object->condominio_id;
                        $detail->unidade_id = $row['id'];
                        $detail->mes_ref = $object->mes_ref;
                        $detail->cobranca = $object->cobranca;
                        $detail->tipo_lancamento = $object->tipo_lancamento;
                        $detail->classe_id = $object->classe_id;
                        $detail->dt_lancamento = $object->dt_lancamento;
                        $detail->dt_vencimento = $object->dt_vencimento;
                        $detail->boleto_status = '1';
            
                        $detail->multa_boleto_cobranca = $object->multa_boleto_cobranca;
                        $detail->juros_boleto_cobranca = $object->juros_boleto_cobranca;
                        
                        if ($row['desconto_titulo'] > 0) {
                            $detail->desconto_boleto_cobranca = $row['desconto_titulo'];
                        
                        } else {
                            $detail->desconto_boleto_cobranca = $object->desconto_boleto_cobranca;    
                        }
                        
                        $detail->dt_limite_desconto_boleto_cobranca = $object->dt_limite_desconto_boleto_cobranca;
                        $detail->conta_fechamento_id = $object->conta_fechamento_id;
                    
                        if ($detail->cobranca == '' or $detail->cobranca == null) {
                            $detail->cobranca = '1';
                        }

                        $unidade = new Unidade($row['id']);
                        $pessoa = new Pessoa($unidade->proprietario_id);
                    
                        $detail->nome_responsavel = $pessoa->nome;
                    
                        if ($row['valor_titulo'] > 0) {
                            $detail->valor = $row['valor_titulo'];
                        
                        } else {                        
                            $detail->valor = $object->valor;
                            
                        }
                        
                        //if (isset(row['texto_adicional_titulo'])) {                    
                        //    $detail->descricao = $object->descricao . ' ' . $row['texto_adicional_titulo'];
                        //} else {
                            $detail->descricao = $object->descricao;
                        //}
                        
                        $detail->usuario = TSession::getValue('username'); 
                        
                        $detail->store();
                    }
                }

            }
            catch(Exception $e)
            {
                new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            }
            
            $this->form->setData($object); // keep form data
            
            TTransaction::close(); // close the transaction
            
            $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
            
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
                $key=$param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new ContasReceber($key); // instantiates the Active Record
                
                // necessário no mysql
                $object->dt_lancamento = TDate::date2br($object->dt_lancamento); 
                $object->dt_vencimento = TDate::date2br($object->dt_vencimento);
                
                $object->valor ? $object->valor = number_format($object->valor, 2, ',', '.') : null;
                
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
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
  
    
}
