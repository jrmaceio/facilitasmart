<?php
/**
 * GrupoContasReceberForm Master/Detail
 * @author  <your name here>
 */
class GrupoContasReceberForm extends TPage
{
    protected $form; // form
    protected $table_details;
    protected $detail_row;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        
        // creates the form
        $this->form = new TForm('form_GrupoContasReceber');
        $this->form->class = 'tform'; // CSS class
        
        $table_master = new TTable;
        $table_master->width = '100%';
        
        $table_master->addRowSet( new TLabel('GrupoContasReceber'), '', '')->class = 'tformtitle';
        
        // add a table inside form
        $table_general = new TTable;
        $table_general->width = '100%';
        
        $frame_general = new TFrame;
        $frame_general->class = 'tframe tframe-custom';
        $frame_general->setLegend('GrupoContasReceber');
        $frame_general->style = 'background:whiteSmoke';
        $frame_general->add($table_general);
        
        $frame_details = new TFrame;
        $frame_details->class = 'tframe tframe-custom';
        $frame_details->setLegend('GrupoContasReceberUnidade');
        
        $table_master->addRow()->addCell( $frame_general )->colspan=2;
        $row = $table_master->addRow();
        $row->addCell( $frame_details );
        
        $this->form->add($table_master);
        
        // master fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $condominio_id = new TEntry('condominio_id');

        // sizes
        $id->setSize('100');
        $descricao->setSize('200');
        $condominio_id->setSize('100');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // add form fields to be handled by form
        $this->form->addField($id);
        $this->form->addField($descricao);
        $this->form->addField($condominio_id);
        
        // add form fields to the screen
        $table_general->addRowSet( new TLabel('Id'), $id );
        $table_general->addRowSet( new TLabel('Descricao'), $descricao );
        $table_general->addRowSet( new TLabel('Condomínio'), $condominio_id );
        
        // detail
        $this->table_details = new TTable;
        $this->table_details-> width = '100%';
        $frame_details->add($this->table_details);
        
        $this->table_details->addSection('thead');
        $row = $this->table_details->addRow();
        
        // detail header
        $row->addCell( new TLabel('Unidade Id') );
        
        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('ico_save.png');

        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onClear')), _t('New'));
        $new_button->setImage('ico_new.png');
        
        // define form fields
        $this->form->addField($save_button);
        $this->form->addField($new_button);
        
        $table_master->addRowSet( array($save_button, $new_button), '', '')->class = 'tformaction'; // CSS class
        
        $this->detail_row = 0;
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open('facilita');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new GrupoContasReceber($key);
                $this->form->setData($object);
                
                $items  = GrupoContasReceberUnidade::where('grupo_id', '=', $key)->load();
                
                $this->table_details->addSection('tbody');
                if ($items)
                {
                    foreach($items  as $item )
                    {
                        $this->addDetailRow($item);
                    }
                    
                    // create add button
                    $add = new TButton('clone');
                    $add->setLabel('Add');
                    $add->setImage('fa:plus-circle green');
                    $add->addFunction('ttable_clone_previous_row(this)');
                    
                    // add buttons in table
                    $this->table_details->addRowSet([$add]);
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Add detail row
     */
    public function addDetailRow($item)
    {
        $uniqid = mt_rand(1000000, 9999999);
        
        // create fields
        $unidade_id = new TEntry('unidade_id[]');

        // set id's
        $unidade_id->setId('unidade_id_'.$uniqid);

        // set sizes
        $unidade_id->setSize('100');
        
        // set row counter
        $unidade_id->{'data-row'} = $this->detail_row;

        // set value
        if (!empty($item->unidade_id)) { $unidade_id->setValue( $item->unidade_id ); }
        
        // create delete button
        $del = new TImage('fa:trash-o red');
        $del->onclick = 'ttable_remove_row(this)';
        
        $row = $this->table_details->addRow();
        // add cells
        $row->addCell($unidade_id);
        
        $row->addCell( $del );
        $row->{'data-row'} = $this->detail_row;
        
        // add form field
        $this->form->addField($unidade_id);
        
        $this->detail_row ++;
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->table_details->addSection('tbody');
        $this->addDetailRow( new stdClass );
        
        // create add button
        $add = new TButton('clone');
        $add->setLabel('Add');
        $add->setImage('fa:plus-circle green');
        $add->addFunction('ttable_clone_previous_row(this)');
        
        // add buttons in table
        $this->table_details->addRowSet([$add]);
    }
    
    /**
     * Save the GrupoContasReceber and the GrupoContasReceberUnidade's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('facilita');
            
            $id = (int) $param['id'];
            $master = new GrupoContasReceber;
            $master->fromArray( $param);
            $master->store(); // save master object
            
            // delete details
            GrupoContasReceberUnidade::where('grupo_id', '=', $master->id)->delete();
            
            if( !empty($param['unidade_id']) AND is_array($param['unidade_id']) )
            {
                foreach( $param['unidade_id'] as $row => $unidade_id)
                {
                    if (!empty($unidade_id))
                    {
                        $detail = new GrupoContasReceberUnidade;
                        $detail->grupo_id = $master->id;
                        $detail->unidade_id = $param['unidade_id'][$row];
                        $detail->store();
                    }
                }
            }
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_GrupoContasReceber', $data);
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
