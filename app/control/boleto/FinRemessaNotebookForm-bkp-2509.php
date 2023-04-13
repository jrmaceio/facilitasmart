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
        $id_movto_remessa = new TDBCombo('id_movto_remessa', 'facilitasmart', 'TipoMovtoRemessa', 'id', '{codigo} - {descricao}','codigo',$criteria_movrem);
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
		$this->form->addFields( [ new TLabel('Condominio') ], [ $id_condominio ] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] , [new TLabel('Conta Corrente')], [$id_conta_corrente] );
        $this->form->addFields( [new TLabel('Layout Cnab')], [$id_layout_cnab] , [new TLabel('Numero Remessa')], [$numero_remessa] );
        $this->form->addFields( [new TLabel('Forma Selecao')], [$forma_selecao] , [new TLabel('Tipo Transacao')], [$tipo_transacao] );
        $this->form->addFields( [new TLabel('Dt Emissao')], [$dt_emissao] , [new TLabel('Dt Vecto Inicial')], [$dt_vecto_inicial] , [new TLabel('Dt Vecto Final')], [$dt_vecto_final] );
        $this->form->addFields( [new TLabel('Carteira')], [$carteira] ,[new TLabel('Movto Remessa')], [$id_movto_remessa] );
        $this->form->addFields( [new TLabel('Codigo Protesto')], [$codigo_protesto] , [new TLabel('Dias Protesto')], [$dias_protesto] );
        $this->form->addFields( [new TLabel('Codigo Baixa Devolucao')], [$codigo_baixa_devolucao] , [new TLabel('Dias Baixa Devolucao')], [$dias_baixa_devolucao] );
        $this->form->addFields(  [new TLabel('Qtde Total Titulos')], [$qtde_total_titulos] , [new TLabel('Vlr Total Titulos')], [$vlr_total_titulos] );
		//$this->form->addFields( [new TLabel('Caminho')], [$caminho] , [new TLabel('Arquivo')], [$arquivo] );

        #titulos
        // -- titulos
        $this->form->appendPage('Titulos');


        // fields
        $detail_id = new THidden('detail-id[]');
        
        $detail_criteria_cond = new TCriteria;
        $detail_criteria_cond->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $detail_id_condominio = new TDBCombo('detail-id_condominio[]', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $detail_criteria_cond);
		
        $detail_criteria_tit = new TCriteria();
        //$detail_criteria_tit->add(new TFilter('id_cliente_erp','=',$unit_erp));
        $detail_id_contas_receber = new TDBCombo('detail-id_contas_receber[]', 'facilitasmart', 'ContasReceber', 'id', 'id','id',$detail_criteria_tit);
        $detail_id_contas_receber->enableSearch();
        
        // set sizes
        $detail_id->setSize('100%');
        $detail_id_condominio->setSize('100%');
        $detail_id_contas_receber->setSize('100%');
        
        // add the fields
        $this->fieldtitulo = new TFieldList;
        $this->fieldtitulo->width = '100%';
        $this->fieldtitulo->name  = 'titulos_list';
        $this->fieldtitulo->addField('<b>Id</b>' , $detail_id ,  ['width' => '10%'] );
        $this->fieldtitulo->addField('<b>Condominio</b>', $detail_id_condominio,  ['width' => '10%'] );
        $this->fieldtitulo->addField('<b>Titulo</b>', $detail_id_contas_receber,  ['width' => '60%'] );

        $this->form->addField( $detail_id );
        $this->form->addField( $detail_id_condominio );
        $this->form->addField( $detail_id_contas_receber );

        $this->fieldtitulo->addHeader();
        $this->fieldtitulo->addDetail( new stdClass );
        $this->fieldtitulo->addDetail( new stdClass );
        $this->fieldtitulo->addCloneAction();
        $this->fieldtitulo->generateAria();

        $this->form->addContent( [$this->fieldtitulo] );
        // -- titulo - fim


        #geral
        if (!empty($id))
        {
            $id->setEditable(FALSE);
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
        
        if (!isset($param['id']))
        {
            $obj->dt_emissao              = $dt_emissao2_obj;
            $obj->id_layout_cnab          = 240;
            $obj->tipo_transacao          = 1;
            $obj->forma_selecao           = 1;
            $obj->carteira                = 2;
            $obj->id_movto_remessa        = 1;
            $obj->codigo_protesto         = 3;
            $obj->dias_protesto           = 5;
            $obj->codigo_baixa_devolucao  = 1;
            $obj->dias_baixa_devolucao    = 60;
        }
        
        TForm::sendData('form_FinRemessa', $obj);
        
        parent::add($container);
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
            $obj = new stdClass;
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('facilitasmart');
                $object = new FinRemessa($key);
                
                //======= titulos
                $titulos  = FinRemessaItem::where('id_fin_remessa', '=', $key)->load();
                $vetor = array();
                $count = 0;
                foreach ($titulos as $val_titulos)
                {
                    TFieldList::addRows('titulos_list', 1);
                    foreach ($val_titulos as $index_titulos=>$value_titulos)
                    {                    
                        $var_titulos   = 'detail-'.$index_titulos;
                        $vetor[$var_titulos][$count] = $value_titulos;
                        $obj->{$var_titulos} = $param[$var_titulos] = $vetor;
                    }
                    $count++;
                    TForm::sendData( 'form_FinRemessa', $vetor, false, false,100 );
                }
                
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
            //$unit_erp = TSession::getValue('cliente_ERP');
            //$unit_emp = TSession::getValue('userempresa');
            $data = $param;
            $data = (object) $data;

			$data->dt_emissao = TDate::date2us($data->dt_emissao );
			
            $master = new FinRemessa;
            $master->fromArray( (array) $data);

            $master->store();
            $data->id = $master->id;
            
            TForm::sendData('form_FinRemessa',$data,false,false);

            //========== Save titulos
            $reg_old_titulos = FinRemessaItem::where('id_fin_remessa', '=', $master->id)->load();
            $keep_titulos = array();
            $key_tot_titulos = $param['detail-id_contas_receber'];
            if( $key_tot_titulos )
            {
                foreach( $key_tot_titulos as $key => $item_id )
                {
                    if($param['detail-id_contas_receber'][$key] != '')
                    {
                        if ( (substr($param['detail-id'][$key],0,1) == 'X' ) || ($param['detail-id'][$key] == '' ) ) {
                            $titulos = new FinRemessaItem;
                        } else {
                            $titulos = FinRemessaItem::find($param['detail-id'][$key]);
                        }
						$titulos->id_fin_remessa    = $master->id;
                        $titulos->id_condominio     = $param['detail-id_condominio'][$key];
                        $titulos->id_contas_receber = $param['detail-id_contas_receber'][$key];
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
                                ->where('dt_emissao', '=', $param['dt_emissao'])
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



}
