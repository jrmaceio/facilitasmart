<?php
/**
 * UnidadesReportAcessoPortaria Report
 * @author  <your name here>
 */
class UnidadesReportAcessoPortaria extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Unidade_report');
        $this->form->setFormTitle('Relatório de Unididades e Acessos a Portaria');

        $bloco_quadra = new TEntry('bloco_quadra');
        $output_type = new TRadioGroup('output_type');
        
        $this->form->addFields( [ new TLabel('Bloco/Quadra') ], [ $bloco_quadra ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );
        
        $output_type->addValidation('Output', new TRequiredValidator);
        
        $bloco_quadra->setSize('100%');
        $output_type->setSize('100%');

        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF', 'xls' => 'XLS'));
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
        // add the action button
        $btn = $this->form->addAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
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
    
    /**
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            // open a transaction with database 'facilita'
            TTransaction::open('facilitasmart');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('Unidade');
            $criteria   = new TCriteria;

            if ($formdata->bloco_quadra)
            {
                $criteria->add(new TFilter('bloco_quadra', '=', "{$formdata->bloco_quadra}"));
            }
            
            $condominio_id = TSession::getValue('id_condominio');
            $criteria->add(new TFilter('condominio_id', '=', "{$condominio_id}"));
            
            $newparam['order'] = 'bloco_quadra, descricao';
            $newparam['direction'] = 'asc';
            
            $criteria->setProperties($newparam); // order, offset
                       
            $objects = $repository->load($criteria, FALSE);
            $format  = $formdata->output_type;
            
            $string = new StringsUtil;
            
           
            if ($objects)
            {
                // largura das colunas
                $widths = array(30,60,160,65,65,65,175,175);
                
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths, $orientation='L');
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths,  $orientation='L');
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
               
                // create the document styles
                $tr->addStyle('title', 'Arial', '8', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '7', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '7', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '10', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '9', 'I',  '#000000', '#A3A3A3');
                $tr->addStyle('cabecalho', 'Arial', '8', 'B',  '#ffffff', '#A3A3A3');
                
                $condominio = new Condominio(TSession::getValue('id_condominio')); 
                
                // qtd colunas
                $colunas = 8;
                
                //cabecalho
                // add a header row
                $tr->addRow();
                $tr->addCell('FacilitaSmart - Gestão Condominial', 'center', 'header', 8);
                $tr->getNativeWriter();
                $tr->addRow();
                $tr->addCell(utf8_decode($condominio->resumo), 'center', 'header', 8);
                $tr->addRow();
                $tr->addCell(utf8_decode('Unidades'), 'center', 'header', 8);
                
                
               
                // add titles row
                $tr->addRow();
                $tr->addCell('Id', 'center', 'title');
                $tr->addCell('Unidade', 'center', 'title');
                $tr->addCell('Proprietario', 'left', 'title');
                $tr->addCell('Telefone', 'center', 'title');
                $tr->addCell('Telefone', 'center', 'title');
                $tr->addCell('Telefone', 'center', 'title');
                
                $tr->addCell('E-Mail', 'center', 'title');
                $tr->addCell('Veículos', 'left', 'title');
              
               
                // uma linha de cada cor conforme datai e datap (linha impar e linha par)
                // controls the background filling
                $colour = FALSE;
                       
                // data rows
                foreach ($objects as $object)
                {
                    $pessoa = new Pessoa($object->proprietario_id);
                    $proprietario = utf8_decode($pessoa->nome);
                    
                    $proprietario_email = $pessoa->email;
                    $proprietario_rg = $pessoa->rg;
                    $proprietario_cpf = $pessoa->cpf_cnpj;
                    
                    $proprietario_telefone1 = $pessoa->telefone1;
                    $proprietario_telefone2 = $pessoa->telefone2;
                    $proprietario_telefone3 = $pessoa->telefone3;
                    //var_dump($object);
            
                    $pessoa = new Pessoa($object->morador_id);
                    $morador = utf8_decode($pessoa->nome);
                    $morador_email = $pessoa->email;
                    $morador_rg = $pessoa->rg;
                    $morador_cpf = $pessoa->cpf_cnpj;
                    $morador_telefone1 = $pessoa->telefone1;
                    $morador_telefone2 = $pessoa->telefone2;
                    $morador_telefone3 = $pessoa->telefone3;
                                  
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'center', $style);
                    $tr->addCell($object->bloco_quadra . '-' . $object->descricao, 'center', $style);
                    
                    if ( $proprietario == 'UNIDADE VAZIA / CADASTRO INCOMPLETO' )
                    {
                      $tr->addCell('', 'left', $style);
                    }
                    else
                    {
                      $tr->addCell($proprietario, 'left', $style);
                    }

                    $tr->addCell($proprietario_telefone1, 'center', $style);
                    $tr->addCell($proprietario_telefone2, 'center', $style);
                    $tr->addCell($proprietario_telefone3, 'center', $style);
                    
                    // culunas de email e veiculos 
                    $tr->addCell( $pessoa->email, 'center', $style);
                    $tr->addCell( ' ', 'center', $style);
                    
                    // verifica se casa alugada, imprime o morador
                    if ( $proprietario != $morador )
                    {
                      //$colour = !$colour;
                      //$style = $colour ? 'datap' : 'datai';
                      $tr->addRow();
                      $tr->addCell('', 'left', $style);
                      $tr->addCell('Morador', 'center', $style);
                      $tr->addCell($morador, 'left', $style);
                      $tr->addCell($morador_telefone1, 'center', $style); 
                      $tr->addCell($morador_telefone2, 'center', $style); 
                      $tr->addCell($morador_telefone3, 'center', $style); 
                      $tr->addCell('', 'left', $style);      
                      $tr->addCell('', 'left', $style);                     
                    }
                    
                    // moradores da unidade
                    $conn8 = TTransaction::get();
                    $sql8 = "SELECT * FROM morador 
                    where unidade_id = {$object->id} order by nome";
                    $colunas8 = $conn8->query($sql8);
                    
                    foreach ($colunas8 as $coluna8)
                    {
                      $tr->addRow();
                      $tr->addCell('', 'left', $style,2);
                      
                      $tr->addCell($coluna8['nome'], 'left', $style);
                      
                      // GRAU PARENTESCO
                      // moradores da unidade
                      $conn9 = TTransaction::get();
                      $sql9 = "SELECT * FROM grau_parentesco 
                      where id = {$coluna8['grau_parentesco_id']}";
                      $colunas9 = $conn9->query($sql9);
                      foreach ($colunas9 as $coluna9)
                      {  
                        $tr->addCell($coluna9['descricao'], 'center', $style);
                      }
                      
                      $tr->addCell($coluna8['telefone1'], 'center', $style);
                      $tr->addCell($coluna8['telefone2'], 'center', $style);
                      
                      $tr->addCell($coluna8['email'], 'center', $style);
                      $tr->addCell('', 'left', $style);
                      
                    }
                    
                    // separador
                    $tr->addRow();
                    $tr->addCell('', 'left', $style,8);
                       
                    $colour = !$colour;
                    $style = $colour ? 'datap' : 'datai';
 
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('d-m-Y h:i:s A'), 'center', 'footer', $colunas);
                
                // stores the file
                if (!file_exists("app/output/UnidadesAcessoPort.{$format}") OR is_writable("app/output/UnidadesAcessoPort.{$format}"))
                {
                    $tr->save("app/output/UnidadesAcessoPort.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/UnidadesAcessoPort.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/UnidadesAcessoPort.{$format}");
                
                // shows the success message
                new TMessage('info', 'Relatório gerado. Por favor, habilite popups no navegador.');
            }
            else
            {
                new TMessage('error', 'Nenhum registro encontrado.');
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
