<?php
/**
 * ContasReceberList Listing
 * @author  <your name here>
 */
class ContasReceberList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_search_contas_receber');
        $this->form->class = 'tform'; // CSS class
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        $this->form->add($table);
        
        // add a row for the form title
        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell( new TLabel('Contas a Receber') )->colspan = 2;
        

        // create the form fields
        $descricao                      = new TEntry('descricao');


        // define the sizes
        $descricao->setSize(200);


        // add one row for each form field
        $table->addRowSet( new TLabel('descricao:'), $descricao );


        $this->form->setFields(array($descricao));


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('contas_receber_filter_data') );
        
        // create two action buttons to the form
        $find_button = TButton::create('find', array($this, 'onSearch'), _t('Find'), 'ico_find.png');
        $new_button  = TButton::create('new',  array('ContasReceberForm', 'onEdit'), _t('New'), 'ico_new.png');
        
        $this->form->addField($find_button);
        $this->form->addField($new_button);
        
        $buttons_box = new THBox;
        $buttons_box->add($find_button);
        $buttons_box->add($new_button);
        
        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'id', 'right', 100);
        $imovel_id   = new TDataGridColumn('imovel_id', 'imovel_id', 'right', 100);
        $mes_ref   = new TDataGridColumn('mes_ref', 'mes_ref', 'left', 200);
        $cobranca   = new TDataGridColumn('cobranca', 'cobranca', 'left', 200);
        $tipo_lancamento   = new TDataGridColumn('tipo_lancamento', 'tipo_lancamento', 'left', 200);
        $classe_id   = new TDataGridColumn('classe_id', 'classe_id', 'right', 100);
        $unidade_id   = new TDataGridColumn('unidade_id', 'unidade_id', 'right', 100);
        $dt_lancamento   = new TDataGridColumn('dt_lancamento', 'dt_lancamento', 'left', 100);
        $dt_vencimento   = new TDataGridColumn('dt_vencimento', 'dt_vencimento', 'left', 100);
        $valor   = new TDataGridColumn('valor', 'valor', 'left', 200);
        $descricao   = new TDataGridColumn('descricao', 'descricao', 'left', 200);


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($imovel_id);
        $this->datagrid->addColumn($mes_ref);
        $this->datagrid->addColumn($cobranca);
        $this->datagrid->addColumn($tipo_lancamento);
        $this->datagrid->addColumn($classe_id);
        $this->datagrid->addColumn($unidade_id);
        $this->datagrid->addColumn($dt_lancamento);
        $this->datagrid->addColumn($dt_vencimento);
        $this->datagrid->addColumn($valor);
        $this->datagrid->addColumn($descricao);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array('ContasReceberForm', 'onEdit'));
        $action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('far:edit blue');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
        $action2->setImage('far:trash-alt red');
        $action2->setField('id');
        
        $action_inf = new TDataGridAction(array($this, 'onPJBankInfo'));
        $action_inf->setLabel('Informação');
        $action_inf->setImage('fa:info-circle blue');
        $action_inf->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        $this->datagrid->addAction($action_inf);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // create the page container
        $container = TVBox::pack( $this->form, $this->datagrid, $this->pageNavigation);
        parent::add($container);
    }
    
    /**
     * 
     */
    public function onPJBankInfo($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            
            TTransaction::open('facilitasmart'); // open a transaction with database
            
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // se titulo estiver movimentado nao permite a operação
            if ( $object->situacao != '0' ) {
              new TMessage('info', 'Título com movimentação, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
            if ( $object->boleto_status != '2' ) {
              new TMessage('info', 'Título não regitrado no PJBank, operação não permitida !'); // success message
              TTransaction::close(); // close the transaction
              return;
                
            }
            
           // if ( $object->nosso_numero == '' ) {
           //   new TMessage('info', 'Título com instrução de boleto (nosso número), operação não permitida !'); // success message
           //   TTransaction::close(); // close the transaction
           //   return;
                
           // }
            
            $condominio = new Condominio($object->condominio_id);
            $unidade = new Unidade($object->unidade_id);
            $pessoa = new Pessoa($unidade->proprietario_id);
            $classe = new PlanoContas($object->classe_id);
            
            // verifica se o condomínio está credenciado no pjbank
            if ($condominio->credencial_pjbank == '') {
                new TMessage('Credenciamento', 'Condomínio não credenciado no PJBank!');
                TTransaction::close(); // close the transaction
                return;
            }
           
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.pjbank.com.br/recebimentos/".$condominio->credencial_pjbank."/transacoes/".$object->pjbank_id_unico,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => false,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "X-CHAVE: " . $condominio->chave_pjbank
              ),));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $pjbank=json_decode($response, true);
                //var_dump($pjbank);
                //var_dump($pjbank[0]['link_info']);
                
                //$link1 = $pjbank[0]['link'];
                //TScript::create("var win = window.open('{$link1}', '_blank'); win.focus();");
                
                $link2 = $pjbank[0]['link_info'];
                TScript::create("var win = window.open('{$link2}', '_blank'); win.focus();");
                
                //file_put_contents('app/output/boleto.pdf', $link2);
                //TPage::openFile('app/output/boleto.pdf');
                //echo $response;
            } 
                            
            TTransaction::close();
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onInlineEdit()
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('facilita'); // open a transaction with database
            $object = new ContasReceber($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('contas_receberList_filter_descricao',   NULL);

        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('contas_receberList_filter_descricao',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('contas_receber_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilita');
            
            // creates a repository for contas_receber
            $repository = new TRepository('ContasReceber');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('contas_receberList_filter_descricao')) {
                $criteria->add(TSession::getValue('contas_receberList_filter_descricao')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // setNumericMask na listagem do datagrid
                    $object->valor = number_format($object->valor, 2, ',', '.');
                    
                    switch ($object->situacao)
                    {
                    case '0':
                        $object->situacao = 'aberto';
                        break;
                    case '1':
                        $object->situacao = 'pago';
                        break;
                    case '2':
                        $object->situacao = 'acordo';
                        break;
                    }
                    
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
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('facilita'); // open a transaction with database
            $object = new ContasReceber($key, FALSE); // instantiates the Active Record
            
            // verifica fechamento
            $status = ContasReceber::retornaStatusFechamento($object->condominio_id, 
                      $object->mes_ref,
                      $object->conta_fechamento_id);
            
            var_dump($status);
                        
            //default = 1 fechado, não permite nada
            if ( $status != 0 or $status == ''){
                new TMessage('info', 'Não existe um fechamento em aberto com o Mês Referência ! Operação Cancelada.');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return;
            }
            ////////////////////////////////////
                          
            if ( $object->situacao == '1') {
                new TMessage('info', 'Título já baixado, não é possível edição !');
                TTransaction::close(); 
                $this->form->setData($object); // mantem os dados digitados;
                return; 
            }
            
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
