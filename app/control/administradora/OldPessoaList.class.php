<?php
/**
 * PessoaList Listing
 * @author  <your name here>
 */
class OldPessoaList extends TStandardList
{
    protected $form; // form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('facilitasmart');
        parent::setActiveRecord('Pessoa');
        parent::addFilterField('id', '=', 'id');
        parent::addFilterField('nome', 'like', 'nome');
        parent::addFilterField('cpf', 'like', 'cpf');
        parent::addFilterField('email', 'like', 'email');
 
        parent::setDefaultOrder('id', 'desc');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('list_Pessoa');

        // define the form title
        $this->form->setFormTitle('Listagem de Pesssoas por Condomínio');
        
        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $email = new TEntry('email');
        
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        //TTransaction::open('facilitasmart');
        //$users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        //foreach ($users as $user)
        //{
            //$nivel_acesso = $user->nivel_acesso_inf;
            //if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                //$criteria = new TCriteria;
                //$criteria->add(new TFilter('id', '=', $user->condominio_id));
                //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);
            //}else {
                //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
            //} 
            
        //}
        //TTransaction::close();
        
        $id->setSize(100);
        $cpf->setSize('70%');
        $nome->setSize('70%');
        $email->setSize('70%');
        //$condominio_id->setSize('70%');

        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome]);
        $this->form->addFields([new TLabel('CPF:')],[$cpf],[new TLabel('Email:')],[$email]);

        //$this->form->addFields([new TLabel('Condomínio:')],[$condominio_id]);
        
        // mantém form preenchido com valores de busca
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        $this->form->addAction('Cadastrar', new TAction(['PessoaForm', 'onEdit']), 'fa:plus #69aa46');
        
       
        // cria a datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $column_id = new TDataGridColumn('id', 'Id', 'center' , '50');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'CPF', 'left');
        $column_email = new TDataGridColumn('email', 'E-mail', 'left');
        
        $unidade_descricao = new TDataGridColumn('unidade_descricao', 'Unidade', 'left');
        
        $unidade_descricao->setTransformer(array($this, 'retornaUnidade'));
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($unidade_descricao);
        
               
        $action_onEdit = new TDataGridAction(array('PessoaForm', 'onEdit'));
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel('Editar');
        $action_onEdit->setImage('fa:pencil-square-o blue');
        $action_onEdit->setField('id');

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array($this, 'onDelete'));
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel('Excluir');
        $action_onDelete->setImage('fa:trash-o red');
        $action_onDelete->setField('id');

        $this->datagrid->addAction($action_onDelete);

        $this->datagrid->createModel();

        // navegador
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

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
        
        
        parent::add($container);
    }
    
    public function retornaUnidade($campo, $object, $row)
    {
        //$campo = Pessoa::getUnidade($object->id); 
        
        //return $campo;
        $contador = 0;
         
        $conn = TTransaction::get();
        $result = $conn->query("select 
                                  id, descricao
                                  from unidade as u
                                  where u.proprietario_id = {$object->id}");
        
        $data = '';
        
        foreach ($result as $row)
        {
            $data = $data . ' ' . $row['id'].'-'.$row['descricao'].'(P)';
            $contador++;
        }
        
        // PESSOA É UM INQUILINO
        if(!$data)
        {
          $conn = TTransaction::get();
          $result = $conn->query("select 
                                	id, descricao
                                    from unidade as u
                                    where u.morador_id = {$object->id}");
          
          $data = '';
        
          foreach ($result as $row)
          {
            $data = $data . ' ' . $row['id'].'-'.$row['descricao'].'(M)';
          }
        
        }
        
        return $data . ' - [' . $contador . '] unidade(s)';
         
         
    } 
 
}
