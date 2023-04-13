<?php
/**
 * @author  <your name here>      
 *
 * $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
 */
class ContasReceberRelParaCobranca extends TPage
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_ContasReceber_report');
        $this->form->class = 'tform'; // change CSS class
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Relatório para Cobrança');
 
        // create the form fields
        $cobranca = new TEntry('cobranca');
        $unidade_id = new TEntry('unidade_id');
        //$dt_vencimento =new TDate('dt_vencimento');
        $unidade_desc = new TEntry('unidade_desc');
        
        
        //$classe_id = new TEntry('classe_id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter("tipo", "=", 'C'));
        $classe_id = new TDBCombo('classe_id', 'facilitasmart', 'PlanoContas', 'id', 'descricao','descricao',$criteria);

        
        //$situacao = new TEntry('situacao');
        $mes_ref = new TEntry('mes_ref');
       
        
        $dt_lancamento = new TDate('dt_lancamento');
        $tipo_lancamento = new TEntry('tipo_lancamento');
        $valor = new TEntry('valor');
        $output_type = new TRadioGroup('output_type');

        //$situacao->setValue('0'); // em aberto 
        
        $classe_id->setSize(50);
        //$situacao->setSize(50);
        $mes_ref->setSize(100);
        $cobranca->setSize(50);
        $unidade_id->setSize(50);
        $unidade_desc->setSize(150);
        //$dt_vencimento->setSize(100);
        $dt_lancamento->setSize(100);
        $valor->setSize(100);
        $tipo_lancamento->setSize(50);
        
        // add the fields
       
        //$this->form->addQuickField('Classe Id', $classe_id,  50 );
        //$this->form->addQuickField('Situacao', $situacao,  50 );
        //$this->form->addQuickField('Mês Ref.', $mes_ref,  50 );
        $this->form->addQuickFields('Classe Id', array($classe_id, 
        new TLabel('Mês Referência'),$mes_ref, new TLabel('Cobrança'),$cobranca,
        new TLabel('Unidade Id'),$unidade_id,
        new TLabel('Unidade'),$unidade_desc,
        ));
        
        //$this->form->addQuickField('Cobranca', $cobranca,  100 );
        //$this->form->addQuickField('Unidade Id', $unidade_id,  50 );
        //$this->form->addQuickField('Dt Vencimento', $dt_vencimento,  50 );
        //$this->form->addQuickFields('Cobrança', array($cobranca, new TLabel('Unidade Id'),$unidade_id, 
        //new TLabel('Dt Vencimento..'),$dt_vencimento));
        
        //$this->form->addQuickField('Tipo Lancamento', $tipo_lancamento,  100 );
        //$this->form->addQuickField('Dt Lancamento', $dt_lancamento,  50 );
        //$this->form->addQuickField('Valor', $valor,  100 );
        $this->form->addQuickFields('Tipo Lancamento', array($tipo_lancamento, 
        new TLabel('Dt Lancamento'), $dt_lancamento,
        new TLabel('Valor'),$valor,
        //new TLabel('Dt Vencimento..'),$dt_vencimento
        ));

        //$this->form->addQuickField('Output', $output_type,  100 , new TRequiredValidator);
 
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        // add the action button
        $this->form->addQuickAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        
        //$container->add(TPanelGroup::pack('Relatório', $this->form));
        $container->add($this->form);
        
        // mostrar o mes ref e imovel selecionado
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('c')); 
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
    
    function Cabecalho()
    {
        //$this->SetY(5);
        //$this->Cell(0, 10, utf8_decode('NOME DA SUA EMPRESA'),0,0,'C');
    }
        
    /**
     * Generate the report
     */
    function onGenerate($param = NULL)
    {
        try
        {
            $string = new StringsUtil;
            // open a transaction with database 'facilitasmart'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('ContasReceber');
            $criteria   = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'unidade_id, dt_vencimento';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->add(new TFilter('condominio_id', '=', TTSession::getValue('id_condominio'))); // add the session filter

            if ($formdata->cobranca)
            {
                $criteria->add(new TFilter('cobranca', 'like', "%{$formdata->cobranca}%"));
            }
            if ($formdata->tipo_lancamento)
            {
                $criteria->add(new TFilter('tipo_lancamento', 'like', "%{$formdata->tipo_lancamento}%"));
            }
            
            if ($formdata->classe_id)
            {
                $criteria->add(new TFilter('classe_id', 'like', "%{$formdata->classe_id}%"));
            }
           
            // situacao = 0
            $criteria->add(new TFilter('situacao', '=', "0"));
            $criteria->add(new TFilter('dt_vencimento', '<=', date('Y-m-d')));  
            
            if ($formdata->mes_ref)
            {
                $criteria->add(new TFilter('mes_ref', 'like', "%{$formdata->mes_ref}%"));
            }
            
            if ($formdata->unidade_id)
            {
                $criteria->add(new TFilter('unidade_id', '=', "{$formdata->unidade_id}"));
            }
            
            if ($formdata->unidade_desc)
            {
                try
                {
                TTransaction::open('facilitasmart');
                $unidades = Unidades::getUnidadesDesc($formdata->unidade_desc);
                TTransaction::close();
                }
                catch (Exception $e)
                {
                new TMessage('error', $e->getMessage());
                }
            
                $criteria->add(new TFilter('unidade_id', 'IN', ($unidades)));
              
                
            }
            
            if ($formdata->dt_lancamento)
            {
                $criteria->add(new TFilter('dt_lancamento', 'like', "%{$formdata->dt_lancamento}%"));
            }
            
            //if ($formdata->dt_vencimento)
            //{
             //   $criteria->add(new TFilter('dt_vencimento', 'like', "%{$formdata->dt_vencimento}%"));
            //}
            
            if ($formdata->valor)
            {
                $criteria->add(new TFilter('valor', 'like', "%{$formdata->valor}%"));
            }

           
            $objects = $repository->load($criteria, FALSE);
                       
            if ($objects)
            {
                $widths = array(230, 200, 75, 80, 50, 40, 40, 50);
                
                //$tr = new TTableWriterPDF($widths);
                        $tr = new TTableWriterPDF($widths, $orientation='L');
                        $fpdf = $tr->getNativeWriter();
                        $fpdf->setHeaderCallback(array($this,'Cabecalho'));
                        $this->Cabecalho($fpdf);
                       //$fpdf->setFooterCallback(array($this,'Rodape')); 
                        

                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#000000', '#ffffff');
                $tr->addStyle('cabecalho', 'Arial', '7', 'B',   '#000000', '#ffffff');
                //$tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#ffffff');
                //$tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('detalhe', 'Arial', '7', '',   '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '7', '',   '#000000', '#ffffff');
                $tr->addStyle('footer', 'Times', '8', 'I',  '#000000', '#ffffff');
                
                // add a header row
                 $tr->addRow();
                //$tr->addCell('ContasReceber', 'center', 'header', 18);
                $tr->addCell('facilitasmart Home Service', 'center', 'title', 8);
                $tr->getNativeWriter();
                $tr->addRow();
                //$tr->addCell('ContasReceber', 'center', 'header', 18);
                $tr->addCell(utf8_decode('Relatório para Cobrança de Inadimplntes'), 'center', 'header', 8);

                $tr->addRow();
                $tr->addCell(utf8_decode(Imoveis::NomeImovel(TSession::getValue('id_imovel'))), 'center', 'header', 8);

                $tr->addRow();
                $tr->addCell('Inadimplência até ' . date('d-m-Y') . ' para contas emitidas e sub judice2', 'left', 'header', 8);
                $tr->addRow();
                $tr->addCell('Correção: não aplicada Multa: 2,00% do montante Juros: 1,00% ao mês Data Base: '. date('d-m-Y'), 'left', 'header', 8);

                // add titles row
                $tr->addRow();
                $tr->addCell('Unidade', 'left', 'cabecalho');
                $tr->addCell('Email', 'center', 'cabecalho');
                $tr->addCell('Tipo Cob.', 'center', 'cabecalho');
                $tr->addCell('Data Cobrança', 'center', 'cabecalho');
                $tr->addCell('Valor', 'right', 'cabecalho');
                
                $tr->addCell('Multa', 'right', 'cabecalho');
                $tr->addCell('Juros', 'right', 'cabecalho');
                $tr->addCell('Vlr Projetado', 'right', 'cabecalho');
                
                //$tr->addCell('Descricao', 'left', 'title');
                //$tr->addCell('Situacao', 'left', 'title');
                //$tr->addCell('Dt Pagamento', 'left', 'title');
                //$tr->addCell('Valor Pago', 'left', 'title');
                //$tr->addCell('Desconto', 'left', 'title');
                //$tr->addCell('Juros', 'left', 'title');
                //$tr->addCell('Multa', 'left', 'title');
                //$tr->addCell('Correcao', 'left', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                $total_valor_lancado = 0;
                $total_valor_projetado = 0;
                $total_multa = 0;
                $total_juros = 0;
                
                $totalG_valor_lancado = 0;
                $totalG_valor_projetado = 0;
                $totalG_multa = 0;
                $totalG_juros = 0;
            
                $unidade = '';
                
                // data rows
                foreach ($objects as $object)
                {
                    if ($unidade !=$object->unidade_id ){
                        // totalza a unidades
                        if ( $total_valor_lancado > 0 ) {
                            $tr->addRow();
                            
                            $tr->addCell($unidade . '-' . $descricao . '-' . $proprietario, 'left', 'detalhe');
                            $tr->addCell($email, 'center', 'detalhe');
                            $tr->addCell('E(   )  C(   )  T(   )', 'center', 'detalhe');
                            $tr->addCell('                    ', 'right', 'detalhe');
                            $tr->addCell(number_format($total_valor_lancado, 2, ',', '.'), 'right', 'detalhe');
                            $tr->addCell(number_format($total_multa, 2, ',', '.'), 'right', 'detalhe');
                            $tr->addCell(number_format($total_juros, 2, ',', '.'), 'right', 'detalhe');
                            $tr->addCell(number_format($total_valor_projetado, 2, ',', '.'), 'right', 'detalhe');
                                                        
                            // zera totalizadores
                            $total_valor_lancado = 0;
                            $total_valor_projetado = 0;
                            $total_multa = 0;
                            $total_juros = 0;
                    
                        }
                        
                        // captura o descricao da unidade
                        $tr->addStyle('normal', 'Arial', '8', 'B',    '#000000', '#ffffff');
                        $descricao = Unidades::RetornaDescricaoUnidade($object->unidade_id);
                        $proprietario = Unidades::RetornaProprietarioUnidade($object->unidade_id);
                        $email = Unidades::RetornaProprietarioEmail($object->unidade_id);
                        
                        if ( $email == 'teste@teste.com.br' ) {
                            $email = ' ';
                            
                        }
                        
                        //$tr->addRow();
                        //$tr->addCell($object->unidade_id . '-' . $descricao . '-' . $proprietario, 'left', 'normal', 9);
                        
                        $unidade = $object->unidade_id;
                    }
                
                    /*                
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'right', $style);
                    ////////////$tr->addCell($object->imovel_id, 'right', $style);
                    $tr->addCell($object->mes_ref, 'center', $style);
                    $tr->addCell($object->cobranca, 'center', $style);
                    //$tr->addCell($object->tipo_lancamento, 'left', $style);
                    
                    $conta = PlanoContas::RetornaPlanoContasCodDescricao($object->classe_id);
                    $tr->addCell($conta, 'left', $style);
                    
                    ///////////////////$tr->addCell($object->unidade_id, 'right', $style);
                    //$tr->addCell($object->dt_lancamento, 'left', $style);
                    $tr->addCell($string->formatDateBR($object->dt_vencimento), 'center', $style);
                    $tr->addCell(number_format($object->valor, 2, ',', '.'), 'right', $style);
                    */
                    
                    // juros 0.033 = 1% ao mes
                    $time_inicial = strtotime($object->dt_vencimento);
                    $time_final = strtotime(date("Y/m/d"));
                    // Calcula a diferença de segundos entre as duas datas:
                    $diferenca = $time_final - $time_inicial; // 19522800 segundos
                    // Calcula a diferença de dias
                    $dias = (int)floor( $diferenca / (60 * 60 * 24)); // 225 dias
                    //var_dump($dias);
                    
                    $juros = 0.033 * $dias;
                    
                    $juros = (($juros * $object->valor) / 100);
                    
                    if($dias<=0)
                    {
                        $multa = 0;
                        $object->dias  = '+'.$dias;
                    }
                    else
                    {
                        $multa = ((2 * $object->valor) / 100);
                        $object->dias  = '-'.$dias;
                    }
                    
                    //$tr->addCell(number_format($multa, 2, ',', '.'), 'right', $style);
                    //$tr->addCell(number_format($juros, 2, ',', '.'), 'right', $style);
                    
                    $valor_projetado = $object->valor + $multa + $juros;
                    
                    //$tr->addCell(number_format($valor_projetado, 2, ',', '.'), 'right', $style);
                    
                    $total_valor_lancado += $object->valor;
                    $total_valor_projetado += $valor_projetado;
                    $total_multa += $multa;
                    $total_juros += $juros;
                    
                    $totalG_valor_lancado += $total_valor_lancado;
                    $totalG_valor_projetado += $total_valor_projetado;
                    $totalG_multa += $total_multa;
                    $totalG_juros += $total_juros;
                    
                                       
                    //$colour = !$colour;
                }
                
                
                
                // footer row
                $tr->addRow();
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                //$tr->addCell('', 'center', 'footer');
                $tr->addCell('Totais:', 'center', 'footer', 4);
                $tr->addCell(number_format($totalG_valor_lancado, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($totalG_multa, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($totalG_juros, 2, ',', '.'), 'right', 'footer');
                $tr->addCell(number_format($totalG_valor_projetado, 2, ',', '.'), 'right', 'footer');
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 8);
                // stores the file
                if (!file_exists("app/output/ContasReceberRelParaCobranca.pdf") OR is_writable("app/output/ContasReceberRelParaCobranca.pdf"))
                {
                    $tr->save("app/output/ContasReceberRelParaCobranca.pdf");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/ContasReceberRelParaCobranca.pdf");
                }
                
                // open the report file
                parent::openFile("app/output/ContasReceberRelParaCobranca.pdf");
                
                // shows the success message
                //new TMessage('info', 'Report generated. Please, enable popups.');
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($formdata);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
