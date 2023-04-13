<?php
/**
 * VistoriaList Listing
 * @author  <your name here>
 */
class VistoriaList extends TStandardList
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
        parent::setActiveRecord('Vistoria');
        parent::addFilterField('id', '=', 'id');
        parent::addFilterField('setor', 'like', 'setor');
        parent::addFilterField('descricao', 'like', 'descricao');
        //parent::addFilterField('fone', 'like', 'fone');
        //parent::addFilterField('email', 'like', 'email');
        //parent::addFilterField('bairro', 'like', 'bairro');
        
        parent::setDefaultOrder('id', 'desc');

        // creates the form
        $this->form = new BootstrapFormBuilder('list_Vistoria');

        // define the form title
        $this->form->setFormTitle('Vistorias');
        
        // faz o tratamento entre tipos de acesso
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        TTransaction::open('facilitasmart');
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        
        foreach ($users as $user)
        {
            if ($user->nivel_acesso_inf == '2' or $user->nivel_acesso_inf == '3' or $user->nivel_acesso_inf == '4') { // gestor, não pode escolher outro condominio
                $criteria = new TCriteria;
                $criteria->add(new TFilter('id', '=', $user->condominio_id));
                $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);        
                $condominio_id->setValue($user->condominio_id);
                $condominio_id->setEditable(FALSE);
                parent::addFilterField('condominio_id', '=', $user->condominio_id);
                
            }else {
               //$condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo');
               $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', '{resumo}','id asc'  );
               
               //$this->form->addAction('Cadastrar', new TAction(['VistoriaForm', 'onEdit']), 'fa:plus #69aa46');
               parent::addFilterField('condominio_id', '=', 'condominio_id');
            } 
                       
        }
        
        TTransaction::close();

        $id = new TEntry('id');
        $setor = new TEntry('setor');
        $descricao = new TEntry('descricao');
        //$fone = new TEntry('fone');
        //$email = new TEntry('email');
        //$bairro = new TEntry('bairro');
        //$cidade_id = new TDBCombo('cidade_id', 'microerp', 'Cidade', 'id', '{nome}','id asc'  );

        $id->setSize(100);
        $setor->setSize('72%');
        $descricao->setSize('72%');
        $condominio_id->setSize('72%');
        
        //$email->setSize('72%');
        //$bairro->setSize('72%');
        //$documento->setSize('72%');
        //$cidade_id->setSize('72%');
                
        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Setor:')],[$setor]);
        
        $this->form->addFields([new TLabel('Condomínio:')],[$condominio_id],[new TLabel('Descrição:')],[$descricao]);
        //$this->form->addFields([new TLabel('Bairro:')],[$bairro],[new TLabel('Cidade:')],[$cidade_id]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-primary');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';

        $column_id = new TDataGridColumn('id', 'Id', 'center' , '50');
        $column_datahora = new TDataGridColumn('data_hora', 'Data/Hora', 'left');
        $column_setor = new TDataGridColumn('setor', 'Setor', 'left');
        $column_condominio_id = new TDataGridColumn('condominio->resumo', 'Condomínio', 'left');
        $column_status = new TDataGridColumn('status', 'Concluída', 'center');
        //$column_cidade_nome = new TDataGridColumn('cidade->nome', 'Cidade', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_datahora);
        $this->datagrid->addColumn($column_setor);
        $this->datagrid->addColumn($column_condominio_id);
        $this->datagrid->addColumn($column_status);

        $column_status->setTransformer( function($value, $object, $row) {
            //$class = ($value=='N') ? 'danger' : 'success';
            //$label = ($value=='N') ? _t('No') : _t('Yes');

            $class = ($value=='C') ? 'success' : 'danger';
            $label = ($value=='C') ? _t('Yes') : _t('No');

            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });

        $action_onEdit = new TDataGridAction(array('VistoriaForm', 'onEdit'));
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
}

