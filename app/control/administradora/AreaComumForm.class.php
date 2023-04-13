<?php
/**
 * AreaComumForm Form
 * @author  <your name here>
 */
class AreaComumForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_AreaComum');
        $this->form->setFormTitle('Area Comum');
        

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $valor_taxa_locacao = new TEntry('valor_taxa_locacao');
        $tem_taxa = new TEntry('tem_taxa');
        $intervalo_locacao = new TEntry('intervalo_locacao');
        $dias_antecedencia_locacao = new TEntry('dias_antecedencia_locacao');
        $hora_inicio = new TEntry('hora_inicio');
        $hora_fim = new TEntry('hora_fim');
        $obrigatorio_lista_presenca = new TEntry('obrigatorio_lista_presenca');
        $capacidade = new TEntry('capacidade');
        $intervalo_entre_locacacoes = new TEntry('intervalo_entre_locacacoes');
        $disponibilidade_segunda = new TCombo('disponibilidade_segunda');
        $disponibilidade_terca = new TCombo('disponibilidade_terca');
        $disponibilidade_quarta = new TCombo('disponibilidade_quarta');
        $disponibilidade_quinta = new TCombo('disponibilidade_quinta');
        $disponibilidade_sexta = new TCombo('disponibilidade_sexta');
        $disponibilidade_sabado = new TCombo('disponibilidade_sabado');
        $disponibilidade_domingo = new TCombo('disponibilidade_domingo');
        $qtd_reservas_dia = new TEntry('qtd_reservas_dia');

        $disponibilidade_segunda->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_terca->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_quarda->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_quinta->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_sexta->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_sabado->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        $disponibilidade_domingo->addItems(array('Y'=>'Sim', 'N'=>'Não'));
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Valor Taxa Locacao') ], [ $valor_taxa_locacao ] );
        $this->form->addFields( [ new TLabel('Tem Taxa') ], [ $tem_taxa ] );
        $this->form->addFields( [ new TLabel('Intervalo Locacao') ], [ $intervalo_locacao ] );
        $this->form->addFields( [ new TLabel('Dias Antecedencia Locacao') ], [ $dias_antecedencia_locacao ] );
        $this->form->addFields( [ new TLabel('Hora Inicio') ], [ $hora_inicio ] );
        $this->form->addFields( [ new TLabel('Hora Fim') ], [ $hora_fim ] );
        $this->form->addFields( [ new TLabel('Obrigatorio Lista Presenca') ], [ $obrigatorio_lista_presenca ] );
        $this->form->addFields( [ new TLabel('Capacidade') ], [ $capacidade ] );
        $this->form->addFields( [ new TLabel('Intervalo Entre Locacacoes') ], [ $intervalo_entre_locacacoes ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Segunda') ], [ $disponibilidade_segunda ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Terca') ], [ $disponibilidade_terca ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Quarta') ], [ $disponibilidade_quarta ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Quinta') ], [ $disponibilidade_quinta ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Sexta') ], [ $disponibilidade_sexta ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Sabado') ], [ $disponibilidade_sabado ] );
        $this->form->addFields( [ new TLabel('Disponibilidade Domingo') ], [ $disponibilidade_domingo ] );
        $this->form->addFields( [ new TLabel('Qtd Reservas Dia') ], [ $qtd_reservas_dia ] );



        // set sizes
        $id->setSize('100%');
        $descricao->setSize('100%');
        $valor_taxa_locacao->setSize('100%');
        $tem_taxa->setSize('100%');
        $intervalo_locacao->setSize('100%');
        $dias_antecedencia_locacao->setSize('100%');
        $hora_inicio->setSize('100%');
        $hora_fim->setSize('100%');
        $obrigatorio_lista_presenca->setSize('100%');
        $capacidade->setSize('100%');
        $intervalo_entre_locacacoes->setSize('100%');
        $disponibilidade_segunda->setSize('100%');
        $disponibilidade_terca->setSize('100%');
        $disponibilidade_quarta->setSize('100%');
        $disponibilidade_quinta->setSize('100%');
        $disponibilidade_sexta->setSize('100%');
        $disponibilidade_sabado->setSize('100%');
        $disponibilidade_domingo->setSize('100%');
        $qtd_reservas_dia->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new AreaComum;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new AreaComum($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
