<?php
/**
 * LayoutCnabForm Form
 * @author  <your name here>
 */
class LayoutCnabForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_LayoutCnab');
        $this->form->setFormTitle('LayoutCnab');
        

        // create the form fields
        $id = new TEntry('id');
        
        $tipo_transacao = new TRadioGroup('tipo_transacao');
        $tipo_transacao->addItems(array('1' => 'Cobrança', '2' => 'Desconto'));
        $tipo_transacao->setLayout('horizontal');
        
        $padrao_arquivo = new TRadioGroup('padrao_arquivo');
        $padrao_arquivo->addItems(array('240' => '240', '400' => '400'));
        $padrao_arquivo->setLayout('horizontal');
                
        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'sigla' , 'sigla');
        
        $remesa_retorno = new TCombo('remesa_retorno');
        $remesa_retorno->addItems(array('1' => 'Remessa', '2' => 'Retorno'));
        
        $tipo_registro = new TCombo('tipo_registro');
        $tipo_registro->addItems(array('0' => 'Header', '1' => 'cobranca' , '3' => 'lote', '5'=> 'trailler-lote', '9' => 'trailler'));
        
        $seguimento = new TEntry('seguimento');     #(?????)
        
        $sequencia = new TEntry('sequencia');
        $descricao = new TEntry('descricao');
        
        $posicao_inicial = new TEntry('posicao_inicial');
        $posicao_final = new TEntry('posicao_final');
        $posicao_total = new TEntry('posicao_total');

        $formato = new TRadioGroup('formato');
        $formato->addItems(array('A' => 'A', 'N' => 'N' , 'B' => 'B', 'X'=> 'X', 'D' => 'D'));
        $formato->setLayout('horizontal');
                
        $padrao = new TEntry('padrao');
        $comando = new TEntry('comando');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Tipo Transacao') ], [ $tipo_transacao ] );
        $this->form->addFields( [ new TLabel('Padrao Arquivo') ], [ $padrao_arquivo ]);
        $this->form->addFields( [ new TLabel('Banco') ], [ $id_banco ] );
        $this->form->addFields( [ new TLabel('Remesa Retorno') ], [ $remesa_retorno ] , [ new TLabel('Tipo Registro') ], [ $tipo_registro ]);
        $this->form->addFields( [ new TLabel('Seguimento') ], [ $seguimento ] , [ new TLabel('Sequencia') ], [ $sequencia ] );
        $this->form->addFields( [ new TLabel('Descricao') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Posicao Inicial') ], [ $posicao_inicial ] ,[ new TLabel('Posicao Final') ], [ $posicao_final ]  );
        $this->form->addFields( [ new TLabel('Posicao Total') ], [ $posicao_total ] );
        $this->form->addFields( [ new TLabel('Formato') ], [ $formato ] );
        $this->form->addFields( [ new TLabel('Padrao') ], [ $padrao ] );
        $this->form->addFields( [ new TLabel('Comando') ], [ $comando ] );



        // set sizes
        $id->setSize('30%');
        //$tipo_transacao->setSize('100%');
        //$padrao_arquivo->setSize('100%');
        $id_banco->setSize('100%');
        $remesa_retorno->setSize('100%');
        $tipo_registro->setSize('100%');
        $seguimento->setSize('100%');
        $sequencia->setSize('100%');
        $descricao->setSize('100%');
        $posicao_inicial->setSize('100%');
        $posicao_final->setSize('100%');
        $posicao_total->setSize('100%');
        //$formato->setSize('100%');
        $padrao->setSize('100%');
        $comando->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addAction(_t('Back'),new TAction(array('LayoutCnabList','onReload')),'far:arrow-alt-circle-left red');
                
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
            
            $object = new LayoutCnab;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $action = new TAction(['LayoutCnabList', 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'),$action);

            //new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
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
                $object = new LayoutCnab($key); // instantiates the Active Record
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
