<?php
/**
 * Pessoa Active Record
 * @author  <your-name-here>
 */
class Pessoa extends TRecord
{
    const TABLENAME = 'pessoa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    //use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('data_nascimento');
        parent::addAttribute('rg');
        parent::addAttribute('cpf');
        parent::addAttribute('cnpj');
        parent::addAttribute('telefone1');
        parent::addAttribute('telefone2');
        parent::addAttribute('telefone3');
        parent::addAttribute('email');
        parent::addAttribute('observacao');
        parent::addAttribute('cep');
        parent::addAttribute('endereco');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('estado');
        parent::addAttribute('condominio_id');
        
        parent::addAttribute('pessoa_fisica_juridica');
        parent::addAttribute('numero');
    }

   
    public static function retornaPessoaCPF($cpf)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cpf', '=', $cpf));
        
        $repository = new TRepository('Pessoa');
        $pessoas = $repository->load($criteria);
        
        $retorno = '';
        
        foreach($pessoas as $pessoa)
        {
            $retorno = $pessoa;
        }
        
        return $retorno;
        
    }
    
    public static function retornaPessoaCNPJ($cnpj)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cnpj', '=', $cnpj));
        
        $repository = new TRepository('Pessoa');
        $pessoas = $repository->load($criteria);
        
        $retorno = '';
        
        foreach($pessoas as $pessoa)
        {
            $retorno = $pessoa;
        }
        
        return $retorno;
        
    }
    
}
