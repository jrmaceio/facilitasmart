<?php
/**
 * FinRemessaNotebookForm Master/Detail
 * @author  <your name here>
 */
class FinRemessaNotebookForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_FinRemessa');
        $this->form->setFormTitle('FinRemessa');

        //$unit_erp = TSession::getValue('cliente_ERP');
        //$unit_emp = TSession::getValue('userempresa');

        #master
        $this->form->appendPage('Principal');
                
        // master fields
        $id = new TEntry('id');

        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
        
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', '{id} - {sigla}');
        $id_banco->enableSearch();

        $id_banco->setChangeAction(new TAction(array($this, 'onChangeBanco')));
        
        $criteria_cta_corrente = new TCriteria();        
        //$criteria_cta_corrente->add(new TFilter('id_cliente_erp','=',$unit_erp));
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', '{id} - {conta} - {agencia} - {titular}','',$criteria_cta_corrente);
        
        $id_layout_cnab = new TRadioGroup('id_layout_cnab');
        $id_layout_cnab->addItems(array('240' => '240', '400' => '400'));
        $id_layout_cnab->setLayout('horizontal');
        
        $id_conta_corrente->setChangeAction(new TAction(array($this, 'onBuscaNumeroRemessa')));
        
        $numero_remessa = new TEntry('numero_remessa');
        
        $dt_emissao = new TDate('dt_emissao');
        $dt_emissao->setMask('dd/mm/yyyy');
        $dt_emissao->setDatabaseMask('yyyy-mm-dd');         
        $dt_emissao->setEditable(FALSE);
        
        $forma_selecao = new TCombo('forma_selecao');
        $forma_selecao->addItems(array('1' => 'Digita Titulo', '2' => 'Busca faixa Vencto'));
        
        $dt_vecto_inicial = new TDate('dt_vecto_inicial');
        $dt_vecto_inicial->setMask('dd/mm/yyyy');
        $dt_vecto_inicial->setDatabaseMask('yyyy-mm-dd');         
        
        $dt_vecto_final = new TDate('dt_vecto_final');
        $dt_vecto_final->setMask('dd/mm/yyyy');
        $dt_vecto_final->setDatabaseMask('yyyy-mm-dd');         
        
        $tipo_transacao = new TCombo('tipo_transacao');
        $tipo_transacao->addItems(array('1' => 'Cobrança', '2' => 'Desconto'));
                
        $carteira = new TCombo('carteira');
        $carteira->addItems(array('1' => 'Simples banco emite', '2' => 'Rapida cedente emite'));

        $criteria_movrem = new TCriteria();
        $criteria_movrem->add(new TFilter('id', '<', '0'));
        $id_movto_remessa = new TDBCombo('id_movto_remessa', 'facilitasmart', 'TipoMovtoRemessa', 'id', '{id} - {codigo} - {descricao}' , 'codigo' , $criteria_movrem);
        $id_movto_remessa->enableSearch();
        
        $codigo_protesto = new TCombo('codigo_protesto');
        $codigo_protesto->addItems(array('1' => 'Protesto', '3' => 'Não protesto'));

        $dias_protesto = new TSpinner('dias_protesto');
        $dias_protesto->setRange(0, 10, 1);
        $dias_protesto->setSize('100%');
                        
        $codigo_baixa_devolucao = new TCombo('codigo_baixa_devolucao');
        $codigo_baixa_devolucao->addItems(array('1' => 'Baixa', '2' => 'Não baixa'));
                
        $dias_baixa_devolucao = new TSpinner('dias_baixa_devolucao');
        $dias_baixa_devolucao->setRange(0, 60, 1);
        $dias_baixa_devolucao->setSize('100%');
                        
        $vlr_total_titulos = new TEntry('vlr_total_titulos');
        $qtde_total_titulos = new TEntry('qtde_total_titulos');
        
        //$caminho = new TEntry('caminho');

        //$arquivo = new TEntry('arquivo');        
        
        //$id_movto_remessa->addValidation('Movto Remessa', new TRequiredValidator);

        // master fields
        $this->form->addFields( [new TLabel('Id')], [$id] );
       //$this->form->addFields( [new TLabel('CliErp')], [$id_cliente_erp] );
       //$this->form->addFields( [new TLabel('Empresa')], [$id_empresa] );
        $this->form->addFields( [new TLabel('Condomínio')], [$id_condominio] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] , [new TLabel('Conta Corrente')], [$id_conta_corrente] );
        $this->form->addFields( [new TLabel('Layout Cnab')], [$id_layout_cnab] , [new TLabel('Numero Remessa')], [$numero_remessa] );
        $this->form->addFields( [new TLabel('Forma Selecao')], [$forma_selecao] , [new TLabel('Tipo Transacao')], [$tipo_transacao] );
        $this->form->addFields( [new TLabel('Dt Emissao')], [$dt_emissao] , [new TLabel('Dt Vecto Inicial')], [$dt_vecto_inicial] , [new TLabel('Dt Vecto Final')], [$dt_vecto_final] );
        $this->form->addFields( [new TLabel('Carteira')], [$carteira] ,[new TLabel('Movto Remessa')], [$id_movto_remessa] );
        $this->form->addFields( [new TLabel('Codigo Protesto')], [$codigo_protesto] , [new TLabel('Dias Protesto')], [$dias_protesto] );
        $this->form->addFields( [new TLabel('Codigo Baixa Devolucao')], [$codigo_baixa_devolucao] , [new TLabel('Dias Baixa Devolucao')], [$dias_baixa_devolucao] );
        $this->form->addFields( [new TLabel('Qtde Total Titulos')], [$qtde_total_titulos] , [new TLabel('Vlr Total Titulos')], [$vlr_total_titulos] );
        //$this->form->addFields( [new TLabel('Caminho')], [$caminho] , [new TLabel('Arquivo')], [$arquivo] );


        #titulos
        // -- titulos
        $this->form->appendPage('Titulos');

        $buttonTitulo1 = TButton::create('find3', [$this, 'onPage'], 'Selecionar Titulos', 'fa:search green');
        $this->form->addFields([$buttonTitulo1]);

        // fields
        $detail_id = new TEntry('detail-id[]');
		
        $detail_criteria_cond = new TCriteria;
        $detail_criteria_cond->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $detail_id_condominio = new TDBCombo('detail-id_condominio[]', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $detail_criteria_cond);
		
        $detail_id_contas_receber = new THidden('detail-id_contas_receber[]');
		
		
        
        /*
        $detail_id_cliente_erp = new THidden('detail-id_cliente_erp[]', 'facilitasmart', 'ClienteErp', 'id', 'nome');
        $detail_id_cliente_erp->setEditable(false);

        $detail_criteria_emp = new TCriteria();
        $detail_criteria_emp->add(new TFilter('id_cliente_erp','=',$unit_erp));        
        $detail_id_empresa = new THidden('detail-id_empresa[]', 'facilitasmart', 'Empresa', 'id', 'nome','nome',$detail_criteria_emp);
        $detail_id_empresa->setEditable(false);
        
        $detail_criteria_tit = new TCriteria();
        $detail_criteria_tit->add(new TFilter('id_cliente_erp','=',$unit_erp));
        $detail_id_fin_titulo = new TDBCombo('detail-id_fin_titulo[]', 'facilitasmart', 'FinTitulo', 'id', '{id} - {docto} - {id_tipo_titulo} - {id_favorecido} - {valor_parcela}','id',$detail_criteria_tit);
        $detail_id_fin_titulo->enableSearch();

        $detail_id = new THidden('detail-dt_emissao[]');
        $detail_id = new THidden('detail-dt_vencto[]');
        $detail_id = new THidden('detail-valor_parcela[]');
        $detail_id = new THidden('detail-valor_liquidado[]');
        $detail_id = new THidden('detail-valor_saldo[]');
        $detail_id = new THidden('detail-observacao[]');
        $detail_id = new THidden('detail-docto_origem[]');
        $detail_id = new THidden('detail-id_origem[]');
        */
        
        $detail_dt_emissao = new TEntry('detail-dt_emissao[]');
        $detail_dt_vencto = new TEntry('detail-dt_vencto[]');
        $detail_valor_parcela = new TEntry('detail-valor_parcela[]');
        
        $detail_valor_liquidado = new TEntry('detail-valor_liquidado[]');
        $detail_valor_saldo = new TEntry('detail-valor_saldo[]');
        
        //$detail_valor_saldo->setExitAction(new TAction(array($this, 'onExitSaldo')));
        
        $detail_observacao = new TEntry('detail-observacao[]');                                            
        $detail_docto_origem = new TEntry('detail-docto_origem[]');
        $detail_id_origem = new TEntry('detail-id_origem[]');
        
        // set sizes
        //$detail_id->setSize('100%');
        //$detail_id_cliente_erp->setSize('100%');
        //$detail_id_empresa->setSize('100%');
        //$detail_id_fin_titulo->setSize('100%');
        
        // add the fields
        $this->fieldtitulo = new TFieldList;
        $this->fieldtitulo->width = '100%';
        $this->fieldtitulo->name  = 'titulos_list';
        
        //$this->fieldtit->addField($detail_check);
        $this->fieldtitulo->addField('<b> Id </b>'            , $detail_id ,  ['width' => '10%'] );
        //$this->fieldtitulo->addField('<b> Titulo </b>'        , $detail_id_contas_receber,  ['width' => '10%'] );
        $this->fieldtitulo->addField('<b> Emissão </b>'       , $detail_dt_emissao);
        $this->fieldtitulo->addField('<b> Vencto </b>'        , $detail_dt_vencto,  ['width' => '10%']);
        $this->fieldtitulo->addField('<b> $ Parcela </b>'     , $detail_valor_parcela,  ['width' => '10%'],  ['sum' => true]);
        $this->fieldtitulo->addField('<b> $ Liquidado </b>'   , $detail_valor_liquidado,  ['sum' => true]);
        $this->fieldtitulo->addField('<b> $ Saldo </b>'       , $detail_valor_saldo,  ['width' => '10%'],  ['sum' => true]);
        $this->fieldtitulo->addField('<b> Obs </b>'           , $detail_observacao);
        $this->fieldtitulo->addField('<b> Docto Origem </b>'  , $detail_docto_origem);
        $this->fieldtitulo->addField('<b> Origem </b>'        , $detail_id_origem);

        

        $this->form->addField( $detail_id );
        $this->form->addField( $detail_id_contas_receber );
        $this->form->addField( $detail_dt_emissao);
        $this->form->addField( $detail_dt_vencto);
        $this->form->addField( $detail_valor_parcela);
        $this->form->addField( $detail_valor_liquidado);
        $this->form->addField( $detail_valor_saldo);
        $this->form->addField( $detail_observacao);
        $this->form->addField( $detail_docto_origem);
        $this->form->addField( $detail_id_origem);
        
        if ( isset($param['method']) AND $param['method'] == 'onCarregaItens' ) { $chave = 'ativa'; } else{ $chave = ''; }
        if (!isset($param['id']) OR empty($param['id']) AND empty($chave))
        {
            $this->fieldtitulo->addHeader();
            $row = $this->fieldtitulo->addDetail( new stdClass );
            $row->del($row->get(count($row->getChildren())-1)); //remove botão excluir
            //$this->fieldtitulo->addCloneAction();
            $this->fieldtitulo->generateAria();
        }

        $scroll_tit = new TScroll;
        $scroll_tit->add($this->fieldtitulo);
        $this->form->addContent( [$scroll_tit] );

        // -- titulo - fim


        #geral
        if (!empty($id))
        {
            $id->setEditable(FALSE);
            BootstrapFormBuilder::hideField('form_FinRemessa', 'id_cliente_erp');
            BootstrapFormBuilder::hideField('form_FinRemessa', 'detail_id_cliente_erp');
            BootstrapFormBuilder::hideField('form_FinRemessa', 'detail_id_empresa');
        }
        
        $btn = $this->form->addAction( _t('Save'),  new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus-circle green');
        //$this->form->addAction( _t('Clear'), new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addAction(_t('Back'),new TAction(array('FinRemessaList','onReload')),'far:arrow-alt-circle-left red');
                
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        $dt_emissao2_obj = date("d/m/Y"); // data atual do facilitasmart

        $obj = new StdClass;     
        //$obj->id_cliente_erp = $id_cliente_erp = TSession::getValue('cliente_ERP');
        //$obj->id_empresa = $id_empresa = TSession::getValue('userempresa');
        //$obj->detail_id_cliente_erp = $detail_id_cliente_erp = TSession::getValue('cliente_ERP');
        //$obj->detail_id_empresa = $detail_id_empresa = TSession::getValue('userempresa');
        
        if (!isset($param['id']))
        {
            $obj->dt_emissao              = $dt_emissao2_obj;
            $obj->id_layout_cnab          = 240;
            $obj->tipo_transacao          = 1;
            $obj->forma_selecao           = 1;
            $obj->carteira                = 2;
            $obj->id_movto_remessa        = 1;
            $obj->codigo_protesto         = 3;
            $obj->dias_protesto           = 0;  //5  -- modificado conforme SICREDI Alagoas em 21/10/2020
            $obj->codigo_baixa_devolucao  = 1;
            $obj->dias_baixa_devolucao    = 60;
            //$obj->caminho = "C:\bancos\ouroverde\sicredi\remessa\\";
        }
        
        TForm::sendData('form_FinRemessa', $obj);
        
        parent::add($container);
        
        TSession::setValue('titulos_remessa',null);
        
    }
    
    
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
    
   
    
    public function onReload($param)
    {
    
        if(null != ( TSession::getValue(__CLASS__.'_items_titulos')))
        {

            $titulos_items = TSession::getValue(__CLASS__.'_items_titulos');        
            $this->titulos_list->clear();
            if ($titulos_items)
            {
                foreach ($titulos_items as $titulos_list_item)
                {
                    $titulos_item = (object) $titulos_list_item;
                    $row_titulos = $this->titulos_list->addItem( $titulos_item );
                    $row_titulos->id = $titulos_list_item['id'];
                }
            }
        }
        
        $this->loaded = TRUE;

    }
    


    public function onEdit($param)
    {
        try
        {
            $unit_erp = TSession::getValue('cliente_ERP');
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('facilitasmart');
                $object = new FinRemessa($key);
                //======= titulos
                $this->fieldtitulo->addHeader();
                $selected_objects = array();
                $titulos = array();
                $vetor = array();
                $count = 0;
                $remessas  = FinRemessaItem::where('id_fin_remessa', '=', $key)->load();
                foreach ($remessas as $remessa)
                {
                    $titulo = ContasReceber::where('id', '=', $remessa->id_contas_receber)->load();
                    $titulos[$remessa->id_contas_receber] = $titulo;
                    $selected_objects[$remessa->id_contas_receber] = $titulo;//->toArray();
                    $this->fieldtitulo->addDetail(new stdClass);
                    foreach ($titulo as $val_tit)
                    {
                       //$reg_return = FinTituloMovtoBusca::setBusca( $val_tit->id , $object->dt_lancamento , $unit_erp , $object->id_empresa );
                       //if ($reg_return->vlrsld > '0.0001')
                       //{
                           //$val_tit->valor_liquidado = $reg_return->vlrliq + $reg_return->vlrdes + $reg_return->vlrdev;
                           //$val_tit->valor_saldo = $reg_return->vlrsld;
                           //$val_tit->valor_liquidacao = $val_tit->valor_juros = $val_tit->valor_descto = $val_tit->valor_devol = 0;
                       //} 
                       $vetor['detail-id'][$count]              = $val_tit->id;
                       $vetor['detail-dt_emissao'][$count]      = Uteis::formataData($val_tit->dt_lancamento,'','');
                	   $vetor['detail-dt_vencto'][$count]       = Uteis::formataData($val_tit->dt_vencimento,'','');
                	   $vetor['detail-valor_parcela'][$count]   = Uteis::numeroBrasil($val_tit->valor);
                	   $vetor['detail-valor_liquidado'][$count] = Uteis::numeroBrasil(0);
                	   $vetor['detail-valor_saldo'][$count]     = Uteis::numeroBrasil($val_tit->valor);
                	   $vetor['detail-observacao'][$count]      = $val_tit->mes_ref; //observacao;
                	   $vetor['detail-docto_origem'][$count]    = 0; //docto;
                	   $vetor['detail-id_origem'][$count]       = 0; //$val_tit->id_docto_origem;
                	   $count++;
                    }
                    TForm::sendData( 'form_FinRemessa', $vetor, false, false, 300 );
                }
                TSession::setValue('titulos_remessa', $selected_objects);
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    


    public function onSave( $param )
    {
        try
        {
            TTransaction::open('facilitasmart');

            //criando log 
            //TTransaction::setLogger(new TLoggerTXT('tmp/log.txt'));

            //$unit_erp = TSession::getValue('cliente_ERP');
            //$unit_emp = TSession::getValue('userempresa');
            $data = $param;
            $data = (object) $data;

            //$this->form->validate(); // validate form data
			
			$data->dt_emissao = TDate::date2us($data->dt_emissao );
            
            $master = new FinRemessa;
            $master->fromArray( (array) $data);
            
            $master->store();

            $data->id = $master->id;
            
            TForm::sendData('form_Empresa',$data,false,false);

            //print "<br>param-> ";
            //print_r ($data);
            //return;

            //========== Save titulos
            $reg_old_titulos = FinRemessaItem::where('id_fin_remessa', '=', $master->id)->load();
            $keep_titulos = array();
            $key_tot_titulos = $param['detail-id'];

            if( $key_tot_titulos )
            {
                foreach( $key_tot_titulos as $key => $item_id )
                {
                    if($param['detail-id'][$key] != '')
                    {
                        //if ( (substr($param['detail-id'][$key],0,1) == 'X' ) || ($param['detail-id'][$key] == '' ) ) {
                        $titulos = new FinRemessaItem;
                        //    print "<br>param-> ";
                    //} else {
                            //$titulos = FinRemessaItem::find($param['detail-id'][$key]);
                            //print "<br>param-> ";
                       
                        $titulos->id_condominio     = $master->id_condominio;
                        $titulos->id_fin_remessa    = $master->id;
                        $titulos->id_contas_receber = $param['detail-id'][$key];
                        $titulos->store();
                        $keep_titulos[] = $titulos->id;
                        }
                    }
                }
            
            if ($reg_old_titulos) {
                foreach ($reg_old_titulos as $reg_old_titulos) {
                    if (!in_array( $reg_old_titulos->id, $keep_titulos)) {
                        $reg_old_titulos->delete();
                    }
                }
            } // fim if ($reg_old_titulos)


            TTransaction::close();
            
            $action = new TAction(['FinRemessaList', 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'),$action);

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    

    /*
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
    */

    public static function onBuscaNumeroRemessa( $param )
    {
        TTransaction::open('facilitasmart');
        
        $reg_rem = FinRemessa::where('id_condominio', '=', $param['id_condominio'])
                                ->where('id_banco', '=', $param['id_banco'])
                                ->where('id_conta_corrente', '=', $param['id_conta_corrente'])
                                ->load();
        $nr_rem = 0;
        foreach ($reg_rem as $value_reg_rem) 
        {
            $nr_rem = $value_reg_rem->numero_remessa + 1; 
        }
        if ($nr_rem == 0) { $nr_rem = 1; }
        TTransaction::close();

        $obj = new StdClass; 
        $obj->numero_remessa = $nr_rem;
        TForm::sendData('form_FinRemessa', $obj);
    }    


    public static function onChangeBanco($param)
    {
        try
        {
            TTransaction::open('facilitasmart');
            if (!empty($param['id_banco']))
            {    
                $criteria = TCriteria::create( ['id_banco' => $param['id_banco'] ] );
                TDBCombo::reloadFromModel('form_FinRemessa', 'id_movto_remessa', 'facilitasmart', 'TipoMovtoRemessa', 'id', '{codigo} - {descricao}', 'codigo', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_FinRemessa', 'id_movto_retorno');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }



    static function onPage($param)
    {
        $arry = array();
        $arry['pre_data']  = $param;
        //$arry['id_tipo_titulo'] = 1;
        TSession::setValue('titulo_criteria_remessa',$arry);
        TSession::setValue('RemessaSelec_filter_data'      ,null);
        TSession::setValue('RemessaSelec_filter_unidade_id' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_ordem'  ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_inicio' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_fim'    ,  NULL);
        AdiantiCoreApplication::loadPage('FinRemessaSelecTituloAux','onReload');
    }



}
