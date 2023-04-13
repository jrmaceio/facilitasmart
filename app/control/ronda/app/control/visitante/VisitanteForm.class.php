<?php
/**
 * VisitanteForm Form
 * @author  <your name here>
 */
class VisitanteForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Visitante');
        $this->form->setFormTitle('Visitante');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $status = new TCombo('status');

        $postofilter = new TCriteria;
        $postofilter->add(new TFilter('unidade_id', '=', TSession::getValue('userunitid')));
        $posto_id = new TDBUniqueSearch('posto_id', 'ronda', 'Posto', 'id', 'descricao', 'descricao asc', $postofilter); 
        
        $motivo_funcao_finalidade = new TEntry('motivo_funcao_finalidade');
        $documento = new TEntry('documento');
        $telefone = new TEntry('telefone');
        $observacao = new TText('observacao');
        $permissao_dom_ini = new TTime('permissao_dom_ini');
        $permissao_dom_fim = new TTime('permissao_dom_fim');
        $permissao_seg_ini = new TTime('permissao_seg_ini');
        $permissao_seg_fim = new TTime('permissao_seg_fim');
        $permissao_ter_ini = new TTime('permissao_ter_ini');
        $permissao_ter_fim = new TTime('permissao_ter_fim');
        $permissao_qua_ini = new TTime('permissao_qua_ini');
        $permissao_qua_fim = new TTime('permissao_qua_fim');
        $permissao_qui_ini = new TTime('permissao_qui_ini');
        $permissao_qui_fim = new TTime('permissao_qui_fim');
        $permissao_sex_ini = new TTime('permissao_sex_ini');
        $permissao_sex_fim = new TTime('permissao_sex_fim');
        $permissao_sab_ini = new TTime('permissao_sab_ini');
        $permissao_sab_fim = new TTime('permissao_sab_fim');
        $data_permitida = new TDate('data_permitida');
        $data_ini = new TTime('data_ini');
        $data_fim = new TTime('data_fim');

        $status->addItems( [ 'Y' => 'Liberado', 'N' => 'Bloqueado' ] ); 

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Status') ], [ $status ] );
        $this->form->addFields( [ new TLabel('Posto') ], [ $posto_id ] );
        $this->form->addFields( [ new TLabel('Motivo/Função/Finalidade') ], [ $motivo_funcao_finalidade ] );
        $this->form->addFields( [ new TLabel('Documento') ], [ $documento ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('Observacao') ], [ $observacao ] );
        $this->form->addFields( [ new TLabel('Permissão Domingo Ini/Fim') ], [ $permissao_dom_ini, $permissao_dom_fim ] );
        $this->form->addFields( [ new TLabel('Permissão Segegunda Ini/Fim') ], [ $permissao_seg_ini , $permissao_seg_fim] );
        $this->form->addFields( [ new TLabel('Permissão Terça Ini/Fim') ], [ $permissao_ter_ini, $permissao_ter_fim ] );
        $this->form->addFields( [ new TLabel('Permissão Quarta Ini/Fim') ], [ $permissao_qua_ini, $permissao_qua_fim ] );
        $this->form->addFields( [ new TLabel('Permissão Quinta Ini/Fim') ], [ $permissao_qui_ini, $permissao_qui_fim] );
        $this->form->addFields( [ new TLabel('Permissão Sexta Ini/Fim') ], [ $permissao_sex_ini, $permissao_sex_fim  ] );
        $this->form->addFields( [ new TLabel('Permissão Sábado Ini/Fim') ], [ $permissao_sab_ini, $permissao_sab_fim ] );
        $this->form->addFields( [ new TLabel('Data Permitida / Ini/Fim') ], [ $data_permitida, $data_ini, $data_fim ] );


        // set sizes
        $id->setSize('20%');
        $nome->setSize('80%');
        $status->setSize('50%');
        $posto_id->setSize('50%');
        $motivo_funcao_finalidade->setSize('50%');
        $documento->setSize('50%');
        $telefone->setSize('50%');
        $observacao->setSize('100%');
        $permissao_dom_ini->setSize('50%');
        $permissao_dom_fim->setSize('50%');
        $permissao_seg_ini->setSize('50%');
        $permissao_seg_fim->setSize('50%');
        $permissao_ter_ini->setSize('50%');
        $permissao_ter_fim->setSize('50%');
        $permissao_qua_ini->setSize('50%');
        $permissao_qua_fim->setSize('50%');
        $permissao_qui_ini->setSize('50%');
        $permissao_qui_fim->setSize('50%');
        $permissao_sex_ini->setSize('50%');
        $permissao_sex_fim->setSize('50%');
        $permissao_sab_ini->setSize('50%');
        $permissao_sab_fim->setSize('50%');
        $data_permitida->setSize('50%');
        $data_ini->setSize('25%');
        $data_fim->setSize('25%');

        $data_permitida->setDatabaseMask('yyyy-mm-dd');
        
        $permissao_dom_ini->setMask('hh:ii');
        $permissao_dom_fim->setMask('hh:ii');
        $permissao_seg_ini->setMask('hh:ii');
        $permissao_seg_fim->setMask('hh:ii');
        $permissao_ter_ini->setMask('hh:ii');
        $permissao_ter_fim->setMask('hh:ii');
        $permissao_qua_ini->setMask('hh:ii');
        $permissao_qua_fim->setMask('hh:ii');
        $permissao_qui_ini->setMask('hh:ii');
        $permissao_qui_fim->setMask('hh:ii');
        $permissao_sex_ini->setMask('hh:ii');
        $permissao_sex_fim->setMask('hh:ii');
        $permissao_sab_ini->setMask('hh:ii');
        $permissao_sab_fim->setMask('hh:ii');
        
        $data_ini->setMask('hh:ii');
        $data_fim->setMask('hh:ii');
        
        $permissao_dom_ini->setValue('00:00:00');
        $permissao_dom_fim->setValue('00:00:00');
        $permissao_seg_ini->setValue('00:00:00');
        $permissao_seg_fim->setValue('00:00:00');
        $permissao_ter_ini->setValue('00:00:00');
        $permissao_ter_fim->setValue('00:00:00');
        $permissao_qua_ini->setValue('00:00:00');
        $permissao_qua_fim->setValue('00:00:00');
        $permissao_qui_ini->setValue('00:00:00');
        $permissao_qui_fim->setValue('00:00:00');
        $permissao_sex_ini->setValue('00:00:00');
        $permissao_sex_fim->setValue('00:00:00');
        $permissao_sab_ini->setValue('00:00:00');
        $permissao_sab_fim->setValue('00:00:00');
        
        $data_ini->setValue('00:00:00');
        $data_fim->setValue('00:00:00');

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
            TTransaction::open('ronda'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Visitante;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            $object->unidade_id = TSession::getValue('userunitid');
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
                TTransaction::open('ronda'); // open a transaction
                $object = new Visitante($key); // instantiates the Active Record
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
