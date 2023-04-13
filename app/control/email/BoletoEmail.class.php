<?php
/**
 *
 */
class BoletoEmail extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_BoletoEmail');

        // define the form title
        $this->form->setFormTitle('Envio de boletos por E-mail');
        
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
                        
        
        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');

        $bloco_quadra = new TEntry('bloco_quadra');

        $id->setSize('20%');
        $descricao->setSize('50%');
        
        $id->setSize(100);
        $descricao->setSize('100%');

        // add the fields
        $this->form->addFields([new TLabel('Id:')], [$id]);
        $this->form->addFields([new TLabel('Bloco/Quadra:')],[$bloco_quadra],[new TLabel('Unidade:')],[$descricao]);
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        //$this->form->addAction('Enviar e-mail(s)', new TAction(array($this, 'onenviarEmail')), 'fa:file green');
        //$this->form->addAction('Limpa Data Envio', new TAction(array($this, 'onlimpaDataEnvio')), 'fa:eraser orange');
        
                     
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        // $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', '50');
        $column_bloco_quadra = new TDataGridColumn('bloco_quadra', 'Bloco Quadra', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Unidade', 'center');
        $column_condominio_id = new TDataGridColumn('condominio_resumo', 'Condomínio', 'center');
        $column_proprietario_id = new TDataGridColumn('proprietario_nome', 'Proprietário', 'right');;
        $column_proprietario_email = new TDataGridColumn('proprietario_email', 'E-mail', 'right');
        $column_dt_ult_aviso = new TDataGridColumn('dt_ult_aviso', 'Dt Envio', 'center');
        //$column_observacao = new TDataGridColumn('observacao', 'Observacao', 'left');
        //$column_envio_boleto = new TDataGridColumn('envio_boleto', 'Envio Boleto', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_bloco_quadra);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_proprietario_id);
        $this->datagrid->addColumn($column_proprietario_email);
        $this->datagrid->addColumn($column_dt_ult_aviso);
        //$this->datagrid->addColumn($column_observacao);
        //$this->datagrid->addColumn($column_envio_boleto);

        $column_dt_ult_aviso->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            
            if ($value == '') {
              return '<i class="fa fa-asterisk  yellow"/>   /  /    ';
            }
            
            if ($value != date('Y-m-d')) {
                return '<i class="fa fa-calendar red"/> '.$date->format('d/m/Y');
            }else {
                return '<i class="fa fa-calendar green"/> '.$date->format('d/m/Y');
            
            }
        });
                
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
     
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

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
            $object = new Unidade($key); // instantiates the Active Record
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
        TSession::setValue('UnidadeList_filter_id',   NULL);
        TSession::setValue('UnidadeList_filter_descricao',   NULL);
        TSession::setValue('UnidadeList_filter_bloco_quadra',   NULL);
        TSession::setValue('UnidadeList_filter_morador_id',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', 'like', "%{$data->id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->descricao) AND ($data->descricao)) {
            $filter = new TFilter('descricao', 'like', "%{$data->descricao}%"); // create the filter
            TSession::setValue('UnidadeList_filter_descricao',   $filter); // stores the filter in the session
        }

        if (isset($data->bloco_quadra) AND ($data->bloco_quadra)) {
            $filter = new TFilter('bloco_quadra', '=', "{$data->bloco_quadra}"); // create the filter
            TSession::setValue('UnidadeList_filter_bloco_quadra',   $filter); // stores the filter in the session
        }


        if (isset($data->morador_id) AND ($data->morador_id)) {
            $filter = new TFilter('morador_id', 'like', "%{$data->morador_id}%"); // create the filter
            TSession::setValue('UnidadeList_filter_morador_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Unidade_filter_data', $data);
        
        $param=array();
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
            
            // creates a repository for Unidade
            $repository = new TRepository('Unidade');
            $limit = 1000;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        
            if (TSession::getValue('UnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_descricao')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_bloco_quadra')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_bloco_quadra')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_morador_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_morador_id')); // add the session filter
            }

            
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
                    $repo = new TRepository('UsuarioCondominio');
                    $usuariocondominios = $repo->where('unidade_id', '=', $object->id)->load();
                    
                    foreach ($usuariocondominios as $usuariocondominio)
                    {
                       //var_dump($usuariocondominio->dt_ult_aviso); 
                       $object->dt_ult_aviso = $usuariocondominio->dt_ult_aviso;
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
    
    public function onlimpaDataEnvio($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'limpaDataEnvio'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Confirma limpar a data de envio ?', $action);
    }
    
    /**
    */
    public function limpaDataEnvio($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Unidade
            $repository = new TRepository('Unidade');
            $limit = 1000;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        
            if (TSession::getValue('UnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_descricao')); // add the session filter
            }

            if (TSession::getValue('UnidadeList_filter_bloco_quadra')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_bloco_quadra')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_morador_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_morador_id')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $repo = new TRepository('UsuarioCondominio');
                    $usuariocondominios = $repo->where('unidade_id', '=', $object->id)->load();
                    
                    foreach ($usuariocondominios as $usuariocondominio)
                    {

                        $usuariocondominio->dt_ult_aviso = '';
                        $usuariocondominio->store();
                                           
                    }
                 }
            }
            
            // close the transaction
            TTransaction::close();
            $this->onReload( $param ); // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    public function onenviarEmail($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'enviarEmail'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Confirma enviar e-mail para as linhas com data diferente de hoje ?', $action);
    }
    
    /**
    */
    public function enviarEmail($param = NULL)
    {
        try
        {
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // creates a repository for Unidade
            $repository = new TRepository('Unidade');
            $limit = 1000;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'descricao';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio'))); // add the session filter
        
            if (TSession::getValue('UnidadeList_filter_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_id')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_descricao')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_descricao')); // add the session filter
            }

            if (TSession::getValue('UnidadeList_filter_bloco_quadra')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_bloco_quadra')); // add the session filter
            }


            if (TSession::getValue('UnidadeList_filter_morador_id')) {
                $criteria->add(TSession::getValue('UnidadeList_filter_morador_id')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    TTransaction::open('permission'); 
                    // inicio teste email
                    $preferences = SystemPreference::getAllPreferences();
                    $mail = new TMail;
                    $mail->setDebug(false);
                    $mail->SMTPSecure = "ssl";
                    $mail->setFrom( trim($preferences['mail_from']), 'FacilitaSmart' );
                    $mail->addAddress( trim('jrmaceio09@gmail.com'), 'TESTE' );
                    $mail->setSubject( 'CronoTeam - Comprovante de inscrição' );
                   // $mail->addAttach( $file, 'Comprovante de inscrição.pdf' );
                    if ($preferences['smtp_auth'])
                    {
                        $mail->SetUseSmtp();
                        $mail->SetSmtpHost($preferences['smtp_host'], $preferences['smtp_port']);
                        $mail->SetSmtpUser($preferences['smtp_user'], $preferences['smtp_pass']);
                    }
                    $body = str_replace('##NOME DO ATLETA##', 'bbbbbbbbbbbbbbb', 'ggggggggggggggggggggg');
                    $body = str_replace('##EVENTO##', 'hhhhhhhhhhhhhhhhhhhhhhh', $body);
                    $mail->setTextBody($body);    
                    sleep(3);            
                    $mail->send();
                    TTransaction::close();
                    // fim teste email

                                  
                       
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
            $this->onReload( $param ); // reload the listing
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
