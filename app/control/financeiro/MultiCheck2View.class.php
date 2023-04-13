<?php
/**
 * Multi Step 2
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class MultiCheck2View extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();
        
        // creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->setHeight(320);
        
        // define the CSS class
        $this->datagrid->class='tdatagrid_table customized-table';
        
        // import the CSS file
        parent::include_css('app/resources/custom-table.css');

        // add the columns
        $this->datagrid->addQuickColumn('Code',        'id',        'right', 70);
        $this->datagrid->addQuickColumn('Description', 'description', 'left', 550);
        
        // creates the datagrid model
        $this->datagrid->createModel();
        
        $back = new TElement('a');
        $back->href = (new TAction(array('ContasReceberSelListBxLote', 'onReload')))->serialize();
        $back->class = 'btn btn-default';
        $back->generator = 'adianti';
        $back->add('<i class="fa fa-backward blue"/> Back');
        
        $panel = new TPanelGroup('Selected items');
        $panel->add( $this->datagrid );
        $panel->addFooter( $back );
        
        // wrap the page content
        parent::add($panel);
    }
    
    /**
     * Load the data into the datagrid
     */
    function onReload()
    {
        $this->datagrid->clear();
        $selected_products = TSession::getValue('_selected_objects');
        
                var_dump($selected_products);
                
        if ($selected_products)
        {
            TTransaction::open('facilita');
            foreach ($selected_products as $selected_product)
            {
                var_dump($selected_product);
                $this->datagrid->addItem( new ContasReceber($selected_product) );
            }
            TTransaction::close();
        }
    }
    
    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
}
