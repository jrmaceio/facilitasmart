<?php
/**
 * VwBoletospjbankList Listing
 * @author  <your name here>
 */
class VwBoletospjbankList extends TPage
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_VwBoletospjbank');
        $this->form->setFormTitle('Emissão de Carnê PJBank (lotes de 24 boletos por vez)');
        

        // create the form fields
        $criteria = new TCriteria;
        $criteria->add(new TFilter("condominio_id", "=", TSession::getValue('id_condominio')));
        $id = new TDBCombo('id', 'facilitasmart', 'Unidade', 'id', 
            '{bloco_quadra}-{descricao} - {proprietario_nome}', 'descricao', $criteria);
            
        $descricao = new TEntry('descricao');
        $nome = new TEntry('nome');
        $valor = new TEntry('valor');
        
        //$dt_vencimento = new TEntry('dt_vencimento');
        $dt_inicio =new TDate('dt_inicio');
        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_fim =new TDate('dt_fim');
        $dt_fim->setMask('dd/mm/yyyy');
        
        $pjbank_pedido_numero = new TEntry('pjbank_pedido_numero');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', '{id} - {descricao}','descricao',$criteria);


        // add the fields
        //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        //$this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        //$this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        //$this->form->addFields( [ new TLabel('Valor') ], [ $valor ] );
        //$this->form->addFields( [ new TLabel('Dt Vencimento') ], [ $dt_vencimento ] );
        //$this->form->addFields( [ new TLabel('Pjbank Pedido Numero') ], [ $pjbank_pedido_numero ] );
        
        $this->form->addFields( [new TLabel('Unidade')], [$id],[new TLabel('Classe')], [$classe_id]);
        
        $this->form->addFields( [new TLabel('Dt. Venc. Inicial')], [$dt_inicio],
                                [new TLabel('Dt. Venc. Final')], [$dt_fim]                                
                            );
                            
        // set sizes
        $id->setSize('100%');
        $descricao->setSize('100%');
        $nome->setSize('100%');
        $valor->setSize('100%');
        $pjbank_pedido_numero->setSize('100%');

        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('VwBoletospjbank_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Carnê', new TAction([$this, 'onCarne']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id Unidade', 'right');
        $column_descricao = new TDataGridColumn('descricao', 'Unidade', 'center');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_mes_ref = new TDataGridColumn('mes_ref', 'Mês Ref.', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'center');
        $column_pjbank_pedido_numero = new TDataGridColumn('pjbank_pedido_numero', 'Pedido', 'center');
        
              
        $column_multa_boleto_cobranca = new TDataGridColumn('multa_boleto_cobranca', 'Multa', 'center');
        $column_juros_boleto_cobranca = new TDataGridColumn('juros_boleto_cobranca', 'Juros', 'center');
        $column_desconto_boleto_cobranca = new TDataGridColumn('desconto_boleto_cobranca', 'Desc.', 'center');
        $column_dt_limite_desconto_boleto_cobranca = new TDataGridColumn('dt_limite_desconto_boleto_cobranca', 'Limite Desc', 'center');
        
        //$column_condominio_id = new TDataGridColumn('condominio_id', 'Condomínio', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_mes_ref);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_pjbank_pedido_numero);
        
        $this->datagrid->addColumn($column_multa_boleto_cobranca);
        $this->datagrid->addColumn($column_juros_boleto_cobranca);
        $this->datagrid->addColumn($column_desconto_boleto_cobranca);
        $this->datagrid->addColumn($column_dt_limite_desconto_boleto_cobranca);
        
        
        //$this->datagrid->addColumn($column_condominio_id);

        $column_dt_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_dt_limite_desconto_boleto_cobranca->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
               
        $column_valor->setTransformer( function($value, $object, $row) {
            return number_format($value, 2, ',', '.');
        });
        
        $column_multa_boleto_cobranca->setTransformer( function($value, $object, $row) {
            if (is_null($value) or $value == '') {
                $value = 0;
            }
            
            return number_format($value, 2, ',', '.');
        });
        
        $column_juros_boleto_cobranca->setTransformer( function($value, $object, $row) {
            if (is_null($value) or $value == '') {
                $value = 0;
            }
            
            return number_format($value, 2, ',', '.');
        });
        
        $column_desconto_boleto_cobranca->setTransformer( function($value, $object, $row) {
            if (is_null($value) or $value == '') {
                $value = 0;
            }
            
            return number_format($value, 2, ',', '.');
        });
        
        // create EDIT action
        //$action_edit = new TDataGridAction(['VwBoletospjbankForm', 'onEdit']);
        ////$action_edit->setUseButton(TRUE);
        ////$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);
        

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            $object = new VwBoletospjbank($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('VwBoletospjbankList_filter_id',   NULL);
        TSession::setValue('VwBoletospjbankList_filter_dt_vencimento',   NULL);
        TSession::setValue('VwBoletospjbankList_filter_pjbank_classe_id',   NULL);


        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('VwBoletospjbankList_filter_id',   $filter); // stores the filter in the session
        }

        if (isset($data->dt_inicio) AND ($data->dt_inicio)) {
            $data->dt_inicio = TDate::date2us($data->dt_inicio);
            $data->dt_fim = TDate::date2us($data->dt_fim);
            
            $filter = new TFilter('dt_vencimento', 'between', $data->dt_inicio, $data->dt_fim); // create the filter
            TSession::setValue('VwBoletospjbankList_filter_dt_vencimento',   $filter); // stores the filter in the session
            
            $data->dt_inicio = TDate::date2br($data->dt_inicio);
            $data->dt_fim = TDate::date2br($data->dt_fim);
        }


        if (isset($data->classe_id) AND ($data->classe_id)) {
            $filter = new TFilter('classe_id', '=', "$data->classe_id"); // create the filter
            TSession::setValue('VwBoletospjbankList_filter_classe_id',   $filter); // stores the filter in the session
        }
               
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('VwBoletospjbank_filter_data', $data);
        
        $param = array();
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
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for VwBoletospjbank
            $repository = new TRepository('VwBoletospjbank');
            $limit = 24; // lotes de 24 em 24 boletos
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('VwBoletospjbankList_filter_id')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_id')); // add the session filter
            }

            if (TSession::getValue('VwBoletospjbankList_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_dt_vencimento')); // add the session filter
            }

            if (TSession::getValue('VwBoletospjbankList_filter_classe_id')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_classe_id')); // add the session filter
            }

            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            //$criteria->add(new TFilter('pjbank_pedido_numero', '!=', '')); // add the session filter
            
            
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
    
    public function onCarne($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for VwBoletospjbank
            $repository = new TRepository('VwBoletospjbank');
            $limit = 24; //lotes de 24 em 24 boletos
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'dt_vencimento';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('VwBoletospjbankList_filter_id')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_id')); // add the session filter
            }


            if (TSession::getValue('VwBoletospjbankList_filter_descricao')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('VwBoletospjbankList_filter_nome')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('VwBoletospjbankList_filter_valor')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_valor')); // add the session filter
            }


            if (TSession::getValue('VwBoletospjbankList_filter_dt_vencimento')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_dt_vencimento')); // add the session filter
            }


            if (TSession::getValue('VwBoletospjbankList_filter_pjbank_pedido_numero')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_pjbank_pedido_numero')); // add the session filter
            }

            if (TSession::getValue('VwBoletospjbankList_filter_classe_id')) {
                $criteria->add(TSession::getValue('VwBoletospjbankList_filter_classe_id')); // add the session filter
            }
            
            // filtros obrigatorios
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
            //$criteria->add(new TFilter('pjbank_pedido_numero', '!=', '')); // add the session filter
            
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }

            $pedidos_numeros = array('pedido_numero');

            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $pedidos_numeros[] = $object->pjbank_pedido_numero;
                    
                }
            }
            
            $condominio = new Condominio($object->condominio_id);

            $data = json_encode(array(
                        'formato'=>'carne',
                        'pedido_numero'=>$pedidos_numeros//"{11420,11444,11468,11492}"
                        ));
            
                        
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/lotes",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank,
                "Content-Type: application/json"
              ),));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
    //          echo $response;
              $pjbank=json_decode($response);
              //var_dump($pjbank);
              //var_dump($pjbank->status);
              //var_dump($pjbank->linkBoleto);
                
               $link2 = $pjbank->linkBoleto;
               TScript::create("var win = window.open('{$link2}', '_blank'); win.focus();");
               //<a href='download.php?file=arquivo.txt'>Link</a>
            } 
            
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
    
    
}
