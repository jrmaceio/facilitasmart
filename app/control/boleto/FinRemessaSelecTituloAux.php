<?php
class FinRemessaSelecTituloAux extends TWindow
{
    use Adianti\Base\AdiantiStandardListTrait;

    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('facilitasmart');
        $this->setActiveRecord('ContasReceber');
        $this->setDefaultOrder('id', 'asc');
        $this->setLimit(10);

        $this->setSize('0.53','0.99'); //Largura, altura;
        $this->setTitle('Seleção Titulos');
        $this->setPosition(null,0.1);

        // creates the form
        $this->form = new BootstrapFormBuilder('form_titulo_remessa');
        //$this->form->setFormTitle('FinTitulo Report');

        $dt_ordem = new TRadioGroup('dt_ordem');
        $cb = array();
        $cb['1'] = '&nbsp&nbspEmissão &nbsp&nbsp&nbsp&nbsp&nbsp';
        $cb['2'] = '&nbsp&nbspVencimento &nbsp&nbsp&nbsp&nbsp&nbsp';
        $dt_ordem->addItems($cb);
        $dt_ordem->setLayout('horizontal');

        $criteria_und = new TCriteria;
        $criteria_und->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $unidade_id = new TDBCombo('unidade_id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria_und);

        $dt_inicial = new TDate('dt_inicio');
        $dt_inicial->setMask('dd/mm/yyyy');
        $dt_inicial->setDatabaseMask('yyyy-mm-dd');

        $dt_final = new TDate('dt_fim');
        $dt_final->setMask('dd/mm/yyyy');
        $dt_final->setDatabaseMask('yyyy-mm-dd');

        $mes_ref = new TEntry('mes_ref');

        $opcao = new TRadioGroup('opcao');
        $cbx = array();
        $cbx['1'] = '&nbsp&nbspCom Remessa &nbsp&nbsp&nbsp&nbsp&nbsp';
        $cbx['2'] = '&nbsp&nbspSem Remessa &nbsp&nbsp&nbsp&nbsp&nbsp';
        $opcao->addItems($cbx);
        $opcao->setLayout('horizontal');

        // add the action button
        $button = TButton::create('buscar', array($this,'onSearch'), 'Filtrar Registros', 'fa:search');
        $button->setFormName('form_titulo_remessa');
        $button->class = 'btn btn-sm btn-primary';
        
        // add the fields        
        $this->form->addFields( [ new TLabel('<b>Ordenação: </b>') ],  [ $dt_ordem ] , [ new TLabel('<b>Unidade: </b>') ],  [ $unidade_id ] );
        $this->form->addFields( [ new TLabel('<b>Dt Inicial: </b>') ],  [ $dt_inicial ] , [ new TLabel('<b>Dt Final: </b>') ],  [ $dt_final ]);
        $this->form->addFields( [ new TLabel('<b>Mês Ref. </b>') ], [$mes_ref] , [ new TLabel('<b>Opção: </b>') ], [$opcao] );
        //$this->form->addFields( [$button] );
        
        // set sizes
        $dt_ordem->setSize('100%');
        $dt_inicial->setSize('100%');
        $dt_final->setSize('100%');
        $mes_ref->setSize('100%');
        $opcao->setSize('100%');

        $btn = $this->form->addAction('Filtrar Registros', new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
                
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_docto = new TDataGridColumn('mes_ref', 'Docto', 'left');
        $column_remessa = new TDataGridColumn('remessa', 'Remessa', 'center');
        $column_dt_vencto = new TDataGridColumn('dt_vencimento', 'Dt Vencto', 'right');
        $column_valor_parcela = new TDataGridColumn('valor', '$ Valor', 'right');
        $column_nosso_numero = new TDataGridColumn('nosso_numero', 'Nosso No.', 'right');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_docto);
        $this->datagrid->addColumn($column_remessa);
        $this->datagrid->addColumn($column_dt_vencto);
        $this->datagrid->addColumn($column_valor_parcela);
        $this->datagrid->addColumn($column_nosso_numero);
        
        $column_id->setTransformer([$this, 'formatRow'] );
        
        $column_dt_vencto->setTransformer( function($value, $object, $row) {
            $date = Uteis::formataData($value,'','');
            return $date;
        });

        $column_valor_parcela->setTransformer(function($value, $object, $row) {
            $value = "R$ " . number_format($value, 2, ",", ".");
            return $value;
        });

        // creates the datagrid actions
        $action1 = new TDataGridAction([$this, 'onSelect'], ['id' => '{id}', 'remessa' => '{remessa}', 'register_state' => 'false']);
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Select', 'far:square fa-fw black');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup;
        $panel->add($this->form);
        //$panel->add($button);
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        //$panel->addHeaderActionLink( 'Filtrar Registros', new TAction([$this, 'onSearch']), 'fa:search' );
        $panel->addHeaderActionLink( 'Mostrar Selecionados', new TAction([$this, 'showResults']), 'fa:table islamic blue' );
        $panel->addHeaderActionLink( 'Limpar Selecionados', new TAction([$this, 'onClear']), 'fa:eraser red' );
        $panel->addHeaderActionLink( '', new TAction([$this, 'onNada']), '' );
        $panel->addHeaderActionLink( '', new TAction([$this, 'onNada']), '' );
        $teste1 = $panel->addHeaderActionLink( '', new TAction([$this, 'onNada']), '' );
        $panel->addHeaderActionLink( 'Confirmar Titulos', new TAction([$this, 'onNext']), 'far:check-circle green' );
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Save the object reference in session
     */
    static function onSelect($param)
    {
        // get the selected objects from session 
        $selected_objects = TSession::getValue('titulos_remessa');
        $pre_data    = TSession::getValue('titulo_criteria_remessa');
        // exclusao da list
        TTransaction::open('facilitasmart');
        $object = new ContasReceber($param['id']); // load the object
        if (isset($selected_objects[$object->id]))
        {
            TScript::create("$('#row_{$object->id}').css('background','').find('i').attr('class','far fa-square fa-fw black');");  //--> nataniel
            
            unset($selected_objects[$object->id]);
            foreach($pre_data['pre_data']['detail-id'] as $key=>$val_row){ if($val_row == $param['id']){ $row = $key;} }
            TFieldList::clearRows('titulos_list', $row, $row);
            if ($row == 0){ array_shift($pre_data['pre_data']['detail-id']);              }else{ unset($pre_data['pre_data']['detail-id'][$row]); }
            //if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_cliente_erp']);  }else{ unset($pre_data['pre_data']['detail-id_cliente_erp'][$row]); }
            //if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_empresa']);      }else{ unset($pre_data['pre_data']['detail-id_empresa'][$row]); }
            if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_contas_receber']);   }else{ unset($pre_data['pre_data']['detail-id_contas_receber'][$row]); }
            if ($row == 0){ array_shift($pre_data['pre_data']['detail-dt_emissao']);      }else{ unset($pre_data['pre_data']['detail-dt_emissao'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-dt_vencto']);       }else{ unset($pre_data['pre_data']['detail-dt_vencto'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-valor_parcela']);   }else{ unset($pre_data['pre_data']['detail-valor_parcela'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-valor_liquidado']); }else{ unset($pre_data['pre_data']['detail-valor_liquidado'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-valor_saldo']);     }else{ unset($pre_data['pre_data']['detail-valor_saldo'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-observacao']);      }else{ unset($pre_data['pre_data']['detail-observacao'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-docto_origem']);    }else{ unset($pre_data['pre_data']['detail-docto_origem'][$row]);}
        	if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_origem']);       }else{ unset($pre_data['pre_data']['detail-id_origem'][$row]);}
    	    $tot_tit = count($pre_data['pre_data']['detail-id']);
    	    if ($row == 0){ TFieldList::addRows('titulos_list', $tot_tit); }
        }
        // adicao no list
        else
        {
           TScript::create("$('#row_{$object->id}').css('background','#abdef9').find('i').attr('class','far fa-check-square fa-fw black');");  //--> nataniel

           if($param['remessa'] != ' - '){ new TMessage('info', 'Titulo já selecionado na '.$param['remessa']); }

           $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
           if (!empty($pre_data['pre_data']['detail-id'][0])){ $tot_per = 1;}else{ $tot_per = 0;}
           end($pre_data['pre_data']['detail-id']); $row = key($pre_data['pre_data']['detail-id']);
           if (empty($pre_data['pre_data']['detail-id'][$row])){$tot_per = 0;}
           $count  = 0;
           if ($tot_per != 0){ TFieldList::addRows('titulos_list', 1); $count  = $row+1; }
           
           //$reg_return = FinTituloMovtoBusca::setBusca( $object->id , $pre_data['pre_data']['dt_emissao'] , $unit_erp , $pre_data['pre_data']['id_empresa'] );
           //if ($reg_return->vlrsld > '0.0001')
           //{
           //    $object->valor_liquidado = $reg_return->vlrliq + $reg_return->vlrdes + $reg_return->vlrdev;
           //    $object->valor_saldo = $reg_return->vlrsld;
           //   $object->valor_liquidacao = $object->valor_juros = $object->valor_descto = $object->valor_devol = 0;
           //}
           
           $pre_data['pre_data']['detail-id'][$count]              = $object->id;
           //$pre_data['pre_data']['detail-id_cliente_erp'][$count]  = $object->id_cliente_erp;
           //$pre_data['pre_data']['detail-id_empresa'][$count]      = $object->id_empresa;
           $pre_data['pre_data']['detail-id_contas_receber'][$count]   = $object->id;
           $pre_data['pre_data']['detail-dt_emissao'][$count]      = Uteis::formataData($object->dt_lancamento,'','');
    	   $pre_data['pre_data']['detail-dt_vencto'][$count]       = Uteis::formataData($object->dt_vencimento,'','');
    	   $pre_data['pre_data']['detail-valor_parcela'][$count]   = Uteis::numeroBrasil($object->valor);
    	   $pre_data['pre_data']['detail-valor_liquidado'][$count] = Uteis::numeroBrasil($object->valor);
    	   $pre_data['pre_data']['detail-valor_saldo'][$count]     = Uteis::numeroBrasil($object->valor);
    	   $pre_data['pre_data']['detail-observacao'][$count]      = $object->descricao;
    	   $pre_data['pre_data']['detail-docto_origem'][$count]    = $object->mes_ref;
    	   $pre_data['pre_data']['detail-id_origem'][$count]       = $object->nosso_numero;
        }
        
        if(empty($pre_data['pre_data']['detail-id']))
    	{
            $pre_data['pre_data']['detail-id'][0] = '';
            //$pre_data['pre_data']['detail-id_cliente_erp'][0] = '';
            //$pre_data['pre_data']['detail-id_empresa'][0] = '';
            $pre_data['pre_data']['detail-id_contas_receber'][0] = '';
            $pre_data['pre_data']['detail-dt_emissao'][0] = '';
            $pre_data['pre_data']['detail-dt_vencto'][0] = '';
            $pre_data['pre_data']['detail-valor_parcela'][0] = '';
            $pre_data['pre_data']['detail-valor_liquidado'][0] = '';
            $pre_data['pre_data']['detail-valor_saldo'][0] = '';
            $pre_data['pre_data']['detail-observacao'][0] = '';
            $pre_data['pre_data']['detail-docto_origem'][0] = '';
            $pre_data['pre_data']['detail-id_origem'][0] = '';
        }
        TSession::setValue('titulos_remessa', $selected_objects); // put the array back to the session
        TSession::setValue('titulo_criteria_remessa',$pre_data);
        TForm::sendData('form_FinRemessa', $pre_data['pre_data'], false, false, 300);
        TTransaction::close();
        // reload datagrids
        //$this->onReload( func_get_arg(0) );
    }
    
    /**
     * Highlight the selected rows
     */
    static function formatRow($value, $object, $row)
    {
        $selected_objects = TSession::getValue('titulos_remessa');
        
        if ($selected_objects)
        {
            if (in_array( (int) $value, array_keys( $selected_objects ) ) )
            {
                $row->style = "background: #abdef9";
                
                $button = $row->find('i', ['class'=>'far fa-square fa-fw black'])[0];
                
                if ($button)
                {
                    $button->class = 'far fa-check-square fa-fw black';
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Show selected records
     */
    static function showResults()
    {
        $datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $datagrid->width = '100%';
        
        $datagrid->addColumn( new TDataGridColumn('id',  'ID',  'left') );
        $datagrid->addColumn( new TDataGridColumn('docto',  'Docto',  'left') );
        $datagrid->addColumn( new TDataGridColumn('dt_vencto',  'Dt Vencto',  'right') );
        $datagrid->addColumn( new TDataGridColumn('valor_parcela',  '$ Parcela',  'right') );
        
        // create the datagrid model
        $datagrid->createModel();
        $i = 0;
        $selected_objects = TSession::getValue('titulos_remessa');

        if ($selected_objects)
        {
            ksort($selected_objects);
            $datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                $datagrid->addItem( (object) $selected_object );
                $i++;
            }
        }

        $row = $datagrid->addRow();
        $row->addCell('Total: ');
        $cell = $row->addCell($i);
        $cell->colspan = 10;
        $cell->align = 'right';

        $win = TWindow::create('Selecionados', 0.5, 0.9);
        $win->add($datagrid);
        $win->show();
    }

    function onClear ($param)
    {
        TSession::setValue('titulos_remessa',null);
        TFieldList::clear('titulos_list');
        $pre_data    = TSession::getValue('titulo_criteria_remessa');
        foreach ($pre_data['pre_data']['detail-id'] as $row=>$value)
        {
            if($row != 0){ unset($pre_data['pre_data']['detail-id'][$row]); }else{$pre_data['pre_data']['detail-id'][$row] = ''; }
            //if($row != 0){ unset($pre_data['pre_data']['detail-id_cliente_erp'][$row]); }else{$pre_data['pre_data']['detail-id_cliente_erp'][$row] = ''; }
            //if($row != 0){ unset($pre_data['pre_data']['detail-id_empresa'][$row]); }else{$pre_data['pre_data']['detail-id_empresa'][$row] = ''; }
            if($row != 0){ unset($pre_data['pre_data']['detail-id_contas_receber'][$row]); }else{$pre_data['pre_data']['detail-id_contas_receber'][$row] = ''; }
            if($row != 0){ unset($pre_data['pre_data']['detail-dt_emissao'][$row]); }else{$pre_data['pre_data']['detail-dt_emissao'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-dt_vencto'][$row]); }else{$pre_data['pre_data']['detail-dt_vencto'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-valor_parcela'][$row]); }else{$pre_data['pre_data']['detail-valor_parcela'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-valor_liquidado'][$row]); }else{$pre_data['pre_data']['detail-valor_liquidado'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-valor_saldo'][$row]); }else{$pre_data['pre_data']['detail-valor_saldo'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-observacao'][$row]); }else{$pre_data['pre_data']['detail-observacao'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-docto_origem'][$row]); }else{$pre_data['pre_data']['detail-docto_origem'][$row] = ''; }
    	    if($row != 0){ unset($pre_data['pre_data']['detail-id_origem'][$row]); }else{$pre_data['pre_data']['detail-id_origem'][$row] = ''; }
        }
        $pre_data['pre_data']['liquida-total'] = Uteis::numeroBrasil(0);
        TForm::sendData('form_FinRemessa', $pre_data['pre_data'], false, false, 300);
        TSession::setValue('titulo_criteria_remessa',$pre_data);
        TSession::setValue('RemessaSelec_filter_data',null);
        TSession::setValue('RemessaSelec_filter_unidade_id' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_ordem'  ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_inicio' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_fim'    ,  NULL);
        TSession::setValue('RemessaSelec_filter_mes_ref'    ,  NULL);
        TSession::setValue('RemessaSelec_filter_opcao'  ,  NULL);
        $this->form->clear(TRUE);
        $this->onReload( func_get_arg(0) );
    }
    function onNext ($param)
    {
        //$selected_objects = TSession::getValue('titulos_remessa');
        //TApplication::postData('form_FinRemessa','FinRemessaNotebookForm','onCarregaItens');
        $id_pg = parent::getId();
        parent::closeWindow($id_pg);
    }
    
    static function onNada ($param)
    {
        
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('facilitasmart');

            $id_condominio = TSession::getValue('id_condominio');
            $reg_remessa = TSession::getValue('titulo_criteria_remessa');
            // instancia um repositório
            $repository = new TRepository($this->activeRecord);
            $limit = isset($this->limit) ? ( $this->limit > 0 ? $this->limit : NULL) : 10;
            // creates a criteria
            $criteria2 = new TCriteria;
            
            $criteria = isset($this->criteria) ? clone $this->criteria : new TCriteria;
            if ($this->order)
            {
                $criteria->setProperty('order',     $this->order);
                $criteria->setProperty('direction', $this->direction);
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('RemessaSelec_filter_unidade_id')) {
                $criteria2->add(TSession::getValue('RemessaSelec_filter_unidade_id')); // add the session filter
            }
            
            if (TSession::getValue('RemessaSelec_filter_dt_inicio')) {
                $criteria2->add(TSession::getValue('RemessaSelec_filter_dt_inicio')); // add the session filter
            }

            if (TSession::getValue('RemessaSelec_filter_dt_fim')) {
                $criteria2->add(TSession::getValue('RemessaSelec_filter_dt_fim')); // add the session filter
            }

            if (TSession::getValue('RemessaSelec_filter_mes_ref')) {
                $criteria2->add(TSession::getValue('RemessaSelec_filter_mes_ref')); // add the session filter
            }
            
            $criteria2->add(new TFilter('situacao','=','0'));  // valida saldo titulo
            $criteria2->add(new TFilter('nosso_numero','!=',''));  // valida se foi emitido
            $criteria2->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));  // valida se foi emitido

            $criteria->add($criteria2, TExpression::AND_OPERATOR);
                                       
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects);
            }
            
            $arry_item = array();
            $remessa_item = FinRemessaItem::all(); //carrega todos os dados da tabela
            $remessa_item_reverse = array_reverse($remessa_item);
            foreach ($remessa_item_reverse as $value)
            {
                $arry_item[$value->id_contas_receber] = $value->id_fin_remessa;
            }
            
            $this->datagrid->clear();

            $opcao = 0; if (TSession::getValue('RemessaSelec_filter_opcao')) { $opcao = TSession::getValue('RemessaSelec_filter_opcao'); }
            
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $object->valor_saldo = $object->valor;  $oke = 0;  $codigo_cor = '';
                    //if ($object->situacao != 0) { $oke = 1; }  // -- valida saldo titulo -- //
                    
                    // -- valida se titulo ja esta em outra remessa -- //
                    //if ($oke == 0)
                    //{

                        //valida remessa
                        if (array_key_exists( $object->id, $arry_item))
                        { $object->remessa = 'Remessa Nr - '.$arry_item[$object->id]; $codigo_cor = '#FF001D'; $oke = 2; }
                        else{ $object->remessa = ' - '; $codigo_cor = '#288218'; } // VERDE    #00FF6B }
                        
                        $okx = 1;
                        if ( ($oke == 0) && ($opcao == 0) ) { $okx = 1; } // nao tem remessa + opcao vazio
                        if ( ($oke == 2) && ($opcao == 0) ) { $okx = 1; } // tem remessa     + opcao vazio
                        if ( ($oke == 0) && ($opcao == 1) ) { $okx = 0; } // nao tem remessa + opcao com
                        if ( ($oke == 2) && ($opcao == 1) ) { $okx = 1; } // tem remessa     + opcao com
                        if ( ($oke == 0) && ($opcao == 2) ) { $okx = 1; } // nao tem remessa + opcao sem
                        if ( ($oke == 2) && ($opcao == 2) ) { $okx = 0; } // tem remessa     + opcao sem
                         
                        if ($okx == 1)
                        {
                            $row = $this->datagrid->addItem($object);
                            $row->id = 'row_'.$object->id;  //--> nataniel
                            $col = $row->get(3); //pegar primeiro filho de $row
                            $col->style = 'color:'. $codigo_cor;
                        }
                         
                    //} // fim  if ($oke == 0)
                    //if ($oke == 0) { $this->datagrid->addItem($object); }
                } // fim  foreach ($objects as $object)
            } // fim  if ($objects)
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            if (isset($this->pageNavigation))
            {
                $this->pageNavigation->setCount($count); // count of records
                $this->pageNavigation->setProperties($param); // order, page
                $this->pageNavigation->setLimit($limit); // limit
            }

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
            $this->pageNavigation->setPage($this->pageNavigation->getPage());
            
            if ( TSession::getValue('RemessaSelec_filter_data') )
            {
                $this->form->setData(TSession::getValue('RemessaSelec_filter_data'));
            }else{ $this->form->clear(); }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    function onSearch ($param)
    {
        $data = $this->form->getData();
        
        TSession::setValue('RemessaSelec_filter_unidade_id' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_ordem'  ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_inicio' ,  NULL);
        TSession::setValue('RemessaSelec_filter_dt_fim'    ,  NULL);
        TSession::setValue('RemessaSelec_filter_mes_ref'    ,  NULL);
        TSession::setValue('RemessaSelec_filter_opcao'  ,  NULL);
        
        if (isset($data->unidade_id) AND ($data->unidade_id)) {
            $filter = new TFilter('unidade_id', '=', "$data->unidade_id"); // create the filter
            TSession::setValue('RemessaSelec_filter_unidade_id',   $filter); // stores the filter in the session
        }

        //valida tipo de data
        if (isset($data->dt_ordem) AND ($data->dt_ordem))
        {
            if ($data->dt_ordem == '1'){ $tipo_data = 'dt_emissao'; }
            if ($data->dt_ordem == '2'){ $tipo_data = 'dt_vencimento'; }
            
            if (isset($data->dt_inicio) AND ($data->dt_inicio)) {
                $filter = new TFilter($tipo_data, '>=', "$data->dt_inicio"); // create the filter
                TSession::setValue('RemessaSelec_filter_dt_inicio',   $filter); // stores the filter in the session
            }
    
            if (isset($data->dt_fim) AND ($data->dt_fim)) {
                $filter = new TFilter($tipo_data, '<=', "$data->dt_fim"); // create the filter
                TSession::setValue('RemessaSelec_filter_dt_fim',   $filter); // stores the filter in the session
            }
        }

        if (isset($data->mes_ref) AND ($data->mes_ref)) {
            $filter = new TFilter('mes_ref', '=', "$data->mes_ref"); // create the filter
            TSession::setValue('RemessaSelec_filter_mes_ref',   $filter); // stores the filter in the session
        }
        
        if (isset($data->opcao) AND ($data->opcao)) {
            //$filter = new TFilter('mes_ref', '=', "$data->mes_ref"); // create the filter
            TSession::setValue('RemessaSelec_filter_opcao',   $data->opcao);
        }

        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('RemessaSelec_filter_data', $data);
        
        $this->onReload($param);
    }


}