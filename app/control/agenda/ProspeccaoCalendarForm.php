<?php
/**
 * ProspeccaoCalendarForm Form
 * @author  <your name here>
 */
class ProspeccaoCalendarForm extends TWindow
{
    protected $form; // form
    private $formFields = [];

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setSize(800, null);
        parent::setTitle('Agenda');
        parent::setProperty('class', 'window_modal');
        
        $this->form = new BootstrapFormBuilder('form_Prospeccao');
        $this->form->setFormTitle('Cadastro de eventos');

        $view = new THidden('view');

        $id = new TEntry('id');
        
        //$criteria = new TCriteria;
        //$criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
        // parece que nao tem criteria $unidade_id = new TDBUniqueSearch('unidade_id', 'facilitasmart', 'Unidade', 'id', '{descricao}','descricao asc', $criteria );
        //$unidade_id = new TDBUniqueSearch('unidade_id', 'facilitasmart', 'Unidade', 'id', 'descricao','descricao asc'  );
        $unidade_id = new TEntry('unidade_id');
        
        $horario_inicial = new TDateTime('horario_inicial');
        $horario_final = new TDateTime('horario_final');
        $titulo = new TEntry('titulo');
        $cor = new TColor('cor');
        $observacao = new TText('observacao');

        $titulo->addValidation('Título', new TRequiredValidator()); 
        $horario_inicial->addValidation('Horário inicial', new TRequiredValidator()); 
        $horario_final->addValidation('Horário final', new TRequiredValidator()); 

        $id->setEditable(false);
        //$unidade_id->setMinLength(2);
        $horario_final->setMask('dd/mm/yyyy hh:ii');
        $horario_inicial->setMask('dd/mm/yyyy hh:ii');

        $horario_final->setDatabaseMask('yyyy-mm-dd hh:ii');
        $horario_inicial->setDatabaseMask('yyyy-mm-dd hh:ii');

        $id->setSize(100);
        $cor->setSize(100);
        $titulo->setSize('72%');
        $unidade_id->setSize('76%');
        $horario_final->setSize(150);
        $horario_inicial->setSize(150);
        $observacao->setSize('76%', 68);
        $id->setEditable(FALSE);
        
        $this->form->addFields([$view]);

        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Unidade:', '#ff0000')],[$unidade_id]);
        $this->form->addFields([new TLabel('Horário inicial:', '#ff0000')],[$horario_inicial],[new TLabel('Horário final:', '#ff0000')],[$horario_final]);
        $this->form->addFields([new TLabel('Título:')],[$titulo],[new TLabel('Cor:')],[$cor]);
        $this->form->addFields([new TLabel('Observação:')],[$observacao]);

        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-primary');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');
        $this->form->addAction('Excluir', new TAction([$this, 'onDelete']), 'fa:trash-o #dd5a43');

        parent::add($this->form);
    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open('facilitasmart');
            
            $this->form->validate();

            $data = $this->form->getData();
            
            $object = new Prospeccao(); 
            $object->fromArray( (array) $data);
            
            $object->condominio_id = TSession::getValue('id_condominio');
            
            $object->store(); 

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data);
            TTransaction::close();
            
            $action = new TAction(['ProspeccaoCalendarFormView', 'onReload']);
            $action->setParameter('view', $data->view);
            $action->setParameter('date', explode(' ', $data->horario_inicial)[0]); 
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $action);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() );
            TTransaction::rollback();
        }
    }
    
    public function onDelete($param = null) 
    {
        if (isset($param['delete']) && $param['delete'] == 1)
        {
            try
            {
                $key = $param['id'];

                TTransaction::open('facilitasmart');
                $object = new Prospeccao($key, FALSE);
                $object->delete();
                TTransaction::close();
                
                $action = new TAction(array('ProspeccaoCalendarFormView', 'onReload'));
                $action->setParameter('view', $param['view']);
                $action->setParameter('date', explode(' ', $param['horario_inicial'])[0]);

                // shows the success message
                new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $action);
            }
            catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage());
                TTransaction::rollback();
            }
        }
        else
        {
            // define the delete action
            $action = new TAction(array($this, 'onDelete'));
            $action->setParameters((array) $this->form->getData()); // pass the key paramsseter ahead
            $action->setParameter('delete', 1);
            
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);   
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
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new Prospeccao($key); // instantiates the Active Record 
                $object->view = $param['view']; 
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
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function onStartEdit($param)
    {
        $this->form->clear();
        
        $data = new stdClass;
        $data->view = $param['view']; // calendar view
        $data->cor = '#3a87ad';

        if ($param['date'])
        {
            if(strlen($param['date']) == '10')
            {
                $param['date'].= ' 09:00';
            }
            
            $data->horario_inicial = str_replace('T', ' ', $param['date']);

            $horario_final = new DateTime($data->horario_inicial);
            $horario_final->add(new DateInterval('PT1H'));
            $data->horario_final = $horario_final->format('Y-m-d H:i:s');

            $this->form->setData( $data );
        }
    }

    public static function onUpdateEvent($param)
    {
        try
        {
            if (isset($param['id']))
            {
                // open a transaction with database 'samples'
                TTransaction::open('facilitasmart');

                $object = new Prospeccao($param['id']);
                $object->horario_inicial = str_replace('T', ' ', $param['start_time']);
                $object->horario_final   = str_replace('T', ' ', $param['end_time']);
                $object->store();

                // close the transaction
                TTransaction::close();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
