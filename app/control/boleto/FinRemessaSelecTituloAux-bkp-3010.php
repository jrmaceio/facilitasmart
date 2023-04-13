<?php
class FinRemessaSelecTituloAux extends TWindow
{
    use Adianti\Base\AdiantiStandardListTrait;

    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('facilitasmart');
        $this->setActiveRecord('ContasReceber');
       // não fez o filtro - pedi para marcelo verificar $this->addFilterField('condominio_id', '=',  TSession::getValue('id_condominio'));
        $this->setDefaultOrder('id', 'asc');
        $this->setLimit(10);

        $this->setSize('770','600'); //Largura, altura;
        $this->setTitle('Seleção Titulos');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_docto = new TDataGridColumn('mes_ref', 'Docto', 'left');
        $column_dt_vencto = new TDataGridColumn('dt_vencimento', 'Dt Vencto', 'right');
        $column_valor_parcela = new TDataGridColumn('valor', '$ Valor', 'right');
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_docto);
        $this->datagrid->addColumn($column_dt_vencto);
        $this->datagrid->addColumn($column_valor_parcela);
        $column_id->setTransformer([$this, 'formatRow'] );
        

        $column_dt_vencto->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $column_valor_parcela->setTransformer(function($value, $object, $row) {
            $value = "R$ " . number_format($value, 2, ",", ".");
            return $value;
        });

        // creates the datagrid actions
        $action1 = new TDataGridAction([$this, 'onSelect'], ['id' => '{id}', 'register_state' => 'false']);
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Select', 'far:square fa-fw black');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
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
    public function onSelect($param)
    {
        //$unit_erp    = TSession::getValue('cliente_ERP');
        // get the selected objects from session 
        $selected_objects = TSession::getValue('titulos_remessa');
        $pre_data    = TSession::getValue('titulo_criteria_remessa');
        // exclusao da list
        TTransaction::open('facilitasmart');
        $object = new ContasReceber($param['id']); // load the object
        if (isset($selected_objects[$object->id]))
        {
            unset($selected_objects[$object->id]);
            foreach($pre_data['pre_data']['detail-id'] as $key=>$val_row){ if($val_row == $param['id']){ $row = $key;} }
            TFieldList::clearRows('titulos_list', $row, $row);
            if ($row == 0){ array_shift($pre_data['pre_data']['detail-id']);              }else{ unset($pre_data['pre_data']['detail-id'][$row]); }
            //if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_cliente_erp']);  }else{ unset($pre_data['pre_data']['detail-id_cliente_erp'][$row]); }
            //if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_empresa']);      }else{ unset($pre_data['pre_data']['detail-id_empresa'][$row]); }
            if ($row == 0){ array_shift($pre_data['pre_data']['detail-id_fin_titulo']);   }else{ unset($pre_data['pre_data']['detail-id_fin_titulo'][$row]); }
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
           $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
           if (!empty($pre_data['pre_data']['detail-id'][0])){ $tot_per = 1;}else{ $tot_per = 0;}
           end($pre_data['pre_data']['detail-id']); $row = key($pre_data['pre_data']['detail-id']);
           if (empty($pre_data['pre_data']['detail-id'][$row])){$tot_per = 0;}
           $count  = 0;
           if ($tot_per != 0){ TFieldList::addRows('titulos_list', 1); $count  = $row+1; }
           
           //$reg_return = FinTituloMovtoBusca::setBusca( $object->id , $pre_data['pre_data']['dt_emissao'] , 
           //                 $unit_erp , $pre_data['pre_data']['id_empresa'] );
           //if ($reg_return->vlrsld > '0.0001')
           //{
           //    $object->valor_liquidado = $reg_return->vlrliq + $reg_return->vlrdes + $reg_return->vlrdev;
           //    $object->valor_saldo = $reg_return->vlrsld;
           //    $object->valor_liquidacao = $object->valor_juros = $object->valor_descto = $object->valor_devol = 0;
           //}
           
           $pre_data['pre_data']['detail-id'][$count]              = $object->id;
           //$pre_data['pre_data']['detail-id_cliente_erp'][$count]  = $object->id_cliente_erp;
           //$pre_data['pre_data']['detail-id_empresa'][$count]      = $object->id_empresa;
           $pre_data['pre_data']['detail-id_fin_titulo'][$count]   = $object->id;
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
            $pre_data['pre_data']['detail-id_fin_titulo'][0] = '';
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
        $this->onReload( func_get_arg(0) );
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
            if($row != 0){ unset($pre_data['pre_data']['detail-id_fin_titulo'][$row]); }else{$pre_data['pre_data']['detail-id_fin_titulo'][$row] = ''; }
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
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if (is_callable($this->transformCallback)) { call_user_func($this->transformCallback, $objects); }

            $this->datagrid->clear();
            
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $object->valor_saldo = $object->valor;  $oke = 0;

                    // -- valida saldo titulo -- //
                    if ($object->situacao != 0) { $oke = 1; }

                    // -- valida se titulo ja esta em outra remessa -- //
                    if ($oke == 0)
                    {
                        $reg_remitem = FinRemessaItem::where('id_condominio', '=', $id_condominio)
                                            ->where('id_contas_receber', '=', $object->id)
                                            ->load();
                        foreach ($reg_remitem as $value_reg_remitem)
                        {
                            if ($value_reg_remitem->id_contas_receber == $object->id) { $oke = 2; }
                        }
                    } // fim  if ($oke == 0)
                    if ($oke == 0) { $this->datagrid->addItem($object); }
                    
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
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}