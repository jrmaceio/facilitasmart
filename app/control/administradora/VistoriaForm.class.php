<?php
/**
 * VistoriaForm Form
 * @author  <your name here>

 Status :

 •  Em aberto.
•   Agendado.
•   Concluído. 
•   Nenhuma.

 */
class VistoriaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Vistoria');
        // define the form title
        
        $this->form->setFormTitle('Vistoria');

        $id = new TEntry('id');
        $data_hora = new TDateTime('data_hora');
        $setor = new TEntry('setor');
        $descricao = new TText('descricao');
        $vistoriante = new TEntry('vistoriante');
        
        $status = new TCombo('status');
        $status->addItems(array(N=>'N�o conclu��da',
                                A=>'Agendado',
                                E=>'Em andamento',
                                C=>'Conclu��da'));

        $condominio_id = new TDBCombo('condominio_id', 'facilitasmart', 'Condominio', 'id', '{resumo}','id asc'  );

        $setor->addValidation('Setor', new TRequiredValidator()); 
        $condominio_id->addValidation('Condomínio id', new TRequiredValidator()); 

        $data_hora->setMask('dd/mm/yyyy hh:ii');

        $data_hora->setDatabaseMask('yyyy-mm-dd hh:ii');

        $id->setEditable(false);

        $id->setSize(100);
        $data_hora->setSize(150);
        $setor->setSize('72%');
        $descricao->setSize('89%', 68);
        $vistoriante->setSize('72%');
        $status->setSize('50%');
        $condominio_id->setSize('72%');
        
        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Data e Hora:', '#ff0000')],[$data_hora]);
        $this->form->addFields([new TLabel('Setor:', '#ff0000')],[$setor]);
        $this->form->addFields([new TLabel('Descrição:')],[$descricao]);
        $this->form->addFields([new TLabel('Vistoriante:')],[$vistoriante]);
        $this->form->addFields([new TLabel('Status:')],[$status]);
        $this->form->addFields([new TLabel('Condomínio:')],[$condominio_id]);

        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-primary');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $container->add($this->form);

        parent::add($container);

    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open('facilitasmart'); // abre transação
            
            $this->form->validate(); // valida dados
            
            $data = $this->form->getData(); // dados do form
            
            $object = new Vistoria(); // create an empty object
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() );
            TTransaction::rollback();
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear();

    }  

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['id']))
            {
                $id = $param['id'];  // get the parameter $id
                TTransaction::open('facilitasmart'); // open a transaction

                $object = new Vistoria($id); // instantiates the Active Record 

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
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
